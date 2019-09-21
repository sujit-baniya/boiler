<?php


namespace App\Services\Media\Models;


use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
class Media extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size',
    ];
    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }
    /**
     * Get the file type.
     *
     * @return string|null
     */
    public function getTypeAttribute()
    {
        return Str::before($this->mime_type, '/') ?? null;
    }
    /**
     * Determine if the file is of the specified type.
     *
     * @param  string  $type
     * @return bool
     */
    public function isOfType(string $type)
    {
        return $this->type === $type;
    }
    /**
     * Get the url to the file.
     *
     * @param  string  $conversion
     * @return mixed
     */
    public function getUrl(string $conversion = '')
    {
        return $this->filesystem()->url($this->getPath($conversion));
    }

    public function getUrlAttribute()
    {
        return url('media/' . $this->name);
    }
    /**
     * Get the full path to the file.
     *
     * @param  string  $conversion
     * @return mixed
     */
    public function getFullPath(string $conversion = '')
    {
        return $this->filesystem()->path($this->getPath($conversion));
    }

    public function read(string $conversion = '')
    {
        $file = $this->getFullPath($conversion);
        $type = $this->mime_type;
        header('Content-Type:'.$type);
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
    }

    public function download()
    {
        $file = $this->getFullPath();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$this->file_name.'"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file); //Absolute URL
    }
    /**
     * Get the path to the file on disk.
     *
     * @param  string  $conversion
     * @return string
     */
    public function getPath(string $conversion = '')
    {
        $directory = $this->getDirectory();
        if ($conversion) {
            $directory .= '/conversions/'.$conversion;
        }
        return $directory.'/'.$this->name;
    }
    /**
     * Get the directory for files on disk.
     *
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->getKey();
    }
    /**
     * Get the filesystem where the associated file is stored.
     *
     * @return Filesystem
     */
    public function filesystem()
    {
        return Storage::disk($this->disk);
    }
}
