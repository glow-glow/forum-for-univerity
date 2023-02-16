<?php

namespace App\Models;

use App\Models\Trait\FilterByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class File extends Model implements HasMedia
{
    use HasFactory;

    use SoftDeletes, FilterByUser, InteractsWithMedia;

    protected $fillable = ['id', 'folder_id', 'created_by_id'];



    /**
     * Set to null if empty
     * @param $input
     */
    public function setFolderIdAttribute($input)
    {
        $this->attributes['folder_id'] = $input ? $input : null;
    }

    /**
     * Set to null if empty
     * @param $input
     */
    public function setCreatedByIdAttribute($input)
    {
        $this->attributes['created_by_id'] = $input ? $input : null;
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id')->withTrashed();
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
