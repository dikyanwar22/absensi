<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['name','address','phone','email','logo_path','website'];

    // Singleton helper
    public static function get(): self
    {
        return static::first() ?? static::create([
            'name' => 'PT. Dicky Anwar',
            'address' => 'Jl Bersama Dicky Anwar no. 35',
        ]);
    }

    public function logoUrl(): ?string
    {
        if (!$this->logo_path) return null;
        // untuk DomPDF butuh path fisik atau base64
        $full = public_path('storage/' . $this->logo_path);
        if (file_exists($full)) return $full;
        $alt = storage_path('app/public/' . $this->logo_path);
        if (file_exists($alt)) return $alt;
        return null;
    }

    public function logoBase64(): ?string
    {
        $path = $this->logoUrl();
        if (!$path || !file_exists($path)) return null;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $mime = $type === 'png' ? 'image/png' : ($type === 'jpg' || $type === 'jpeg' ? 'image/jpeg' : 'image/png');
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
