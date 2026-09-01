<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use App\Models\GuideItem;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'website_name' => SiteSetting::getValue('website_name', 'PILKETOS'),
            'school_name' => SiteSetting::getValue('school_name', 'SMKN 1 Bangsri'),
            'logo_path' => SiteSetting::getValue('logo_path', ''),
            'favicon_path' => SiteSetting::getValue('favicon_path', ''),
            'email' => SiteSetting::getValue('email', ''),
            'whatsapp' => SiteSetting::getValue('whatsapp', ''),
            'address' => SiteSetting::getValue('address', ''),
            'footer_text' => SiteSetting::getValue('footer_text', 'PILKETOS'),
            'copyright_text' => SiteSetting::getValue('copyright_text', 'All rights reserved.'),
            'footer_year' => SiteSetting::getValue('footer_year', date('Y')),
            'election_active' => SiteSetting::getValue('election_active', '1'),
            'show_statistics' => SiteSetting::getValue('show_statistics', '1'),
            'show_finished' => SiteSetting::getValue('show_finished', '1'),
            'confirm_delete' => SiteSetting::getValue('confirm_delete', '1'),
        ];

        $guideItems = [
            'siswa' => GuideItem::targeted('siswa')->get(),
            'guru' => GuideItem::targeted('guru')->get(),
        ];

        $aboutSections = AboutPage::all()->pluck('content', 'section');

        return view('admin.settings.index', compact('settings', 'guideItems', 'aboutSections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'website_name' => ['nullable', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,svg', 'max:5120'],
            'favicon_file' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp,svg', 'max:2048'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'footer_year' => ['nullable', 'integer'],
            'election_active' => ['nullable', 'boolean'],
            'show_statistics' => ['nullable', 'boolean'],
            'show_finished' => ['nullable', 'boolean'],
            'confirm_delete' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'required_if:logout_all,1'],
            'logout_all' => ['nullable', 'boolean'],
        ]);

        foreach (['election_active', 'show_statistics', 'show_finished', 'confirm_delete'] as $booleanSetting) {
            $data[$booleanSetting] = $request->boolean($booleanSetting);
        }

        foreach ([
            'logo_file' => 'logo_path',
            'favicon_file' => 'favicon_path',
        ] as $fileField => $settingKey) {
            if ($request->hasFile($fileField)) {
                $oldFile = SiteSetting::getValue($settingKey, '');
                if ($oldFile && ! filter_var($oldFile, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($oldFile);
                }

                $path = $request->file($fileField)->store('branding', 'public');
                $data[$settingKey] = $path;
            }
        }

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                continue;
            }
            if ($key === 'logout_all') {
                continue;
            }
            if (in_array($key, ['logo_file', 'favicon_file'], true)) {
                continue;
            }
            SiteSetting::setValue($key, $value);
        }

        if (! empty($data['password'])) {
            $user = Auth::user();
            $user->password = Hash::make($data['password']);
            $user->save();
        }

        if (! empty($data['logout_all']) && ! empty($data['password'])) {
            Auth::logoutOtherDevices($data['password']);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function storeGuide(Request $request)
    {
        $data = $request->validate([
            'target' => ['required', 'in:siswa,guru'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'order' => ['nullable', 'integer'],
            'guide_id' => ['nullable', 'integer', 'exists:guide_items,id'],
        ]);

        if (! empty($data['guide_id'])) {
            $guide = GuideItem::findOrFail($data['guide_id']);
            $guide->fill($data);
            $guide->save();
        } else {
            GuideItem::create($data);
        }

        return back()->with('success', 'Panduan berhasil disimpan.');
    }

    public function destroyGuide(GuideItem $guideItem)
    {
        $guideItem->delete();

        return back()->with('success', 'Panduan berhasil dihapus.');
    }

    public function storeAbout(Request $request)
    {
        $data = $request->validate([
            'content' => ['nullable', 'array'],
            'content.*' => ['nullable', 'string'],
            'custom_keys' => ['nullable', 'array'],
            'custom_keys.*' => ['nullable', 'string', 'max:255'],
            'custom_titles' => ['nullable', 'array'],
            'custom_titles.*' => ['nullable', 'string', 'max:255'],
            'deleted_custom_keys' => ['nullable', 'array'],
            'deleted_custom_keys.*' => ['nullable', 'string', 'max:255'],
            'about_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        foreach ($data['deleted_custom_keys'] ?? [] as $section) {
            AboutPage::where('section', $section)->delete();
        }

        foreach ($data['custom_keys'] ?? [] as $index => $oldKey) {
            $title = trim($data['custom_titles'][$index] ?? '');
            $content = ($data['content'] ?? [])[$oldKey] ?? '';
            if ($title === '') {
                continue;
            }

            $newKey = 'custom_' . (Str::slug($title) ?: 'section_' . ($index + 1));
            if ($newKey !== $oldKey) {
                AboutPage::where('section', $oldKey)->delete();
            }
            AboutPage::updateOrCreate(['section' => $newKey], ['content' => $content]);
        }

        if ($request->hasFile('about_image_file')) {
            $oldImage = AboutPage::where('section', 'about_image')->value('content');
            if ($oldImage && ! filter_var($oldImage, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($oldImage);
            }

            $imagePath = $request->file('about_image_file')->store('about', 'public');
            AboutPage::updateOrCreate(['section' => 'about_image'], ['content' => $imagePath]);
        }

        return back()->with('success', 'Informasi tentang website berhasil disimpan.');
    }
}
