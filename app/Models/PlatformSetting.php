<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlatformSetting extends Model {
    protected $fillable = ['key','value','group'];
    public static function get(string $key, $default=null) {
        $r = static::where('key',$key)->first();
        return $r ? $r->value : $default;
    }
    public static function set(string $key, $value, string $group='general') {
        return static::updateOrCreate(['key'=>$key],['value'=>$value,'group'=>$group]);
    }
}
