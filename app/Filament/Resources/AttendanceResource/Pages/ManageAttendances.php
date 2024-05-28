<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\AttArea;
use App\Models\AttendanceIn;
use ArberMustafa\FilamentLocationPickrField\Forms\Components\LocationPickr;
use Carbon\Carbon;
use DateTime;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManageAttendances extends ManageRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->icon("fas-fingerprint")
            ->label("Attendance In")
            ->form([
                Section::make("Attendance In")
                ->columns([
                    'sm' => 1,
                    'xl' => 2,
                    '2xl' => 3,
                ])
                ->schema([
                    Select::make('user_id')
                        ->label('Choose User')
                        ->searchable()
                        ->preload()
                        ->relationship('user', 'name')
                        ->required(),
                    Select::make('area_id')
                        ->label('Choose Area')
                        ->searchable()
                        ->preload()
                        ->options(AttArea::all()->pluck('name', 'id'))
                        ->required(),
                    Select::make('att_group_schedule_id')
                        ->label('Choose Schedule')
                        ->searchable()
                        ->preload()
                        ->relationship('schedule', 'date_work')
                        ->required(),
                    LocationPickr::make('location')
                    ->columnSpanFull(),
                    FileUpload::make('photo')
                        ->image()
                        ->imageEditor()
                        ->directory('attendance-in')
                        // ->required()
                        ->columnSpanFull(),
                ])
            ])
            ->mutateFormDataUsing(function (array $data): array {
                $jam_sekarang = Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                $data['time'] = $jam_sekarang;
                return $data;
            })
            ->using(function (array $data): Model {
                $validateArea = AttArea::whereHas('user', function ($query) use ($data){
                    $query->where('user_id', $data['user_id']);
                });
                $area = $validateArea->count('*');
                $areaDetail = $validateArea->first();
                $q = new AttendanceIn();
                if($area > 0){
                    $lat1 = $areaDetail->lat;
                    $lon1 = $areaDetail->lng;

                    // Koordinat Bandung
                    $lat2 = $data['location']['lat'];
                    $lon2 = $data['location']['lng'];
                    $theta = $lon1 - $lon2;
                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                    $dist = acos($dist);
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    $meters = round($miles * 1609.344, 2);
                    if((float)$meters < (float)$areaDetail->radius){
                        $c = DB::table('att_group_schedule as ags')
                        ->join('att_times as at','ags.att_time_id','=','at.id')
                        ->where('ags.id','=', $data['att_group_schedule_id'])
                        ->select('at.in')
                        ->first();
                        $waktu_masuk_seharusnya = new DateTime($c->in);
                        $waktu_masuk_sebenarnya = new DateTime($data['time']);
                        $selisih = $waktu_masuk_sebenarnya->diff($waktu_masuk_seharusnya);
                        $status = strtotime($data['time']) > strtotime($c->in) ?'late':'unlate';
                        
                        $q->user_id = $data['user_id'];
                        $q->att_group_schedule_id = $data['att_group_schedule_id'];
                        $q->location = $data['location'];
                        $q->time = $data['time'];
                        $q->difference = $selisih->format('%H:%I:%S');
                        $q->photo = $data['photo'];
                        $q->status = $status;
                        $q->save();
                        Notification::make()
                            ->title('Presence successfuly')
                            ->success()
                            ->send();
                        return $q;
                    }else{
                        Notification::make()
                            ->title('The absence radius distance is insufficient, the minimum absence radius distance is '.$areaDetail->radius.' mmeters.')
                            ->danger()
                            ->send();
                            return $q;
                    }
                }else{
                    Notification::make()
                        ->title('Location is not available/invalid location for this user')
                        ->danger()
                        ->send();
                        return $q;
                }
            })
            ->successNotification(null),
        ];
    }
}
