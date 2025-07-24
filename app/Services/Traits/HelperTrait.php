<?php

namespace App\Services\Traits;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HelperTrait
{

    protected $fileRepository;


    public function generateCode(int $length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    public function generateUuid()
    {
        return (string) Str::uuid() . '-' . time();
    }

    public function generateOrderedUuid()
    {
        return (string) Str::orderedUuid();
    }

    public static function changeEnvironmentVariable($key,$value)
    {
        $path = base_path('.env');

        if(is_bool(env($key)))
        {
            $old = env($key)? 'true' : 'false';
        }
        elseif(env($key)===null)
        {
            $old = 'null';
        }
        else
        {
            $old = env($key);
        }

        if (file_exists($path))
        {
            file_put_contents($path, str_replace(
            "$key=".$old, "$key=".$value, file_get_contents($path)));
        }
    }

    /**
     * Sauvegarder un fichier
     *
     * @param string document_path
     * @param Object model associé au fichier
     * @return Fichier
     */
    public function storeFile($file, $document_path, $model, $height, $description, $shared = null)
    {
            $filenameWithExt = $file->getClientOriginalName();
            $filename = strtolower(str_replace(' ', '-',time() . '-'. $filenameWithExt));
            $path ="{$document_path}/" . $filename;

            if(!$shared)
            {
                /* $fichier = Fichier::create([
                    'nom'                 => $filename,
                    'chemin'                 => "upload/".$path,
                    'fichiertable_type'    => $model ? get_class($model) : 'Autre',
                    'fichiertable_id'      => $model ? $model->id : 1,
                    'auteurId'             => Auth::user()->id,
                    'description'         => $description,
                    'programmeId'         => auth()->user()->programmeId
                  ]); */

                if($description == "image" || $description == "logo" || $description == "photo" || $description == "preuves")
                {
                    Storage::disk('public')->put( "upload/".$path, $file->getContent());
                }

                else
                {
                    Storage::disk('local')->put( "upload/".$path, $file->getContent());
                }

                //$path = $file->move("/storage/documents/{$document_path}/", $filename);

                /*if($height)
                {
                    $image = Image::make($path);
                    $image->resize($image->width(), $height);
                    $image->save($path);
                }*/
            }

            /* else
            {
                $fichier = Fichier::find($shared['fichierId']);

                Fichier::create([
                    'nom'                 => $fichier->nom,
                    'chemin'                 => "upload/".$fichier->chemin,
                    'fichiertable_type'    => $model ? get_class($model) : 'Autre',
                    'fichiertable_id'      => $model ? $model->id : 1,
                    'auteurId'             => Auth::user()->id,
                    'programmeId'         => auth()->user()->programmeId,
                    'description'         => $fichier->description,
                    'sharedId'           => $shared['userId']
                  ]);
            }

            return $fichier; */

    }

    public function formatageNotification(Model $notification, User $user)
    {
        $note = [
            'id' => $notification->id,
            'texte' => $notification->data['texte'],
            'module' => $notification->data['module'],
            'module_id' => $notification->data['id']
        ];

        return [
            "notification" => $note,
            "notifiable_id" => $notification->notifiable_id,
            "unread" => $user->unreadNotifications->count()
        ];
    }

    public function getCurrentTrimestre()
    {
        $currentDate = Carbon::now(); // Get the current date
        $currentMonth = $currentDate->month;
        $currentTrimestre = 1;

        if ($currentMonth >= 1 && $currentMonth <= 3) {
            $currentTrimestre;
        } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
            $currentTrimestre = 2;
        } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
            $currentTrimestre = 3;
        } elseif ($currentMonth >= 10 && $currentMonth <= 12) {
            $currentTrimestre = 4;
        }

        return $currentTrimestre;
    }

    public function getCurrentTrimestreDates(int $trimestre = 1, $annee = null)
    {
        $currentDate = Carbon::parse("$annee-01-01") ?? Carbon::now(); // Get the current date
        $currentMonth = $currentDate->month;

        if ($trimestre == 1/*  || ($currentMonth >= 1 && $currentMonth <= 3) */) {
            $startDate = Carbon::create($currentDate->year, 1, 1);
            $endDate = Carbon::create($currentDate->year, 3, 31);
        } elseif ($trimestre == 2/*  || ($currentMonth >= 4 && $currentMonth <= 6) */) {
            $startDate = Carbon::create($currentDate->year, 4, 1);
            $endDate = Carbon::create($currentDate->year, 6, 30);
        } elseif ($trimestre == 3/*  || ($currentMonth >= 7 && $currentMonth <= 9) */) {
            $startDate = Carbon::create($currentDate->year, 7, 1);
            $endDate = Carbon::create($currentDate->year, 9, 30);
        } elseif ($trimestre == 4){
            $startDate = Carbon::create($currentDate->year, 10, 1);
            $endDate = Carbon::create($currentDate->year, 12, 31);
        } else {
            $startDate = Carbon::create($currentDate->year, 1, 1);
            $endDate = Carbon::create($currentDate->year, 3, 31);
        }

        return [
            $startDate->toDateString(),
            $endDate->toDateString()
        ];
    }
}
