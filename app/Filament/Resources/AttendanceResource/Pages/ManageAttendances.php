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
            // absen masuk
            Actions\Action::make('in')
            ->icon("fas-fingerprint")
            ->label("Attendance In")
            ->color('success')
            ->outlined()
            ->fillForm(function (): array {
                $v = DB::table('att_group_schedule as ags')
                    ->join('user_att_groups as uag','ags.user_att_group_id','=','uag.id')
                    ->join('user_att_group_relations as uagr','uag.id','=','uagr.user_att_group_id')
                    ->where('uagr.user_id','=', auth()->id())
                    ->where('ags.date_work','=', date('Y-m-d'))
                    ->select('ags.id')
                    ->first();
                return [
                    'user_id' => auth()->id(),
                    'att_group_schedule_id' => $v ? $v->id : null
                ];
            })
            ->form([
                Section::make("Make sure the location coordinates, work schedule, and location where you work are in accordance with what has been determined!")
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
                        ->required()
                        ->columnSpanFull(),
                ])
            ])
            ->mutateFormDataUsing(function (array $data): array {
                $jam_sekarang = Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                $data['time'] = $jam_sekarang;
                $data['departement_id'] = auth()->user()->departemen_id;
                $data['position_id'] = auth()->user()->position_id;
                $data['level_id'] = auth()->user()->level_id;
                return $data;
            })
            ->action(function (array $data): void {
                $c = DB::table('att_group_schedule as ags')
                            ->join('att_times as at','ags.att_time_id','=','at.id')
                            ->where('ags.id','=', $data['att_group_schedule_id'])
                            ->select('at.in', 'ags.date_work')
                            ->first();
                $q = new AttendanceIn();
                $tanggal_validasi = new DateTime($c->date_work);
                $tanggal_hari_ini = new DateTime();
                if ($tanggal_validasi->format('Y-m-d') === $tanggal_hari_ini->format('Y-m-d')) {
                    if ((int)$data['user_id'] === auth()->id()) {
                        $validateArea = AttArea::whereHas('user', function ($query) use ($data){
                            $query
                                ->where('user_id', $data['user_id']);
                        })
                        ->where('id', $data['area_id']);
                        $area = $validateArea->count('*');
                        $areaDetail = $validateArea->first();
                        if($area > 0){
                            $lat1 = floatval($areaDetail->lat);
                            $lon1 = floatval($areaDetail->lng);
        
                            // Koordinat lokasi
                            $lat2 = $data['location']['lat'];
                            $lon2 = $data['location']['lng'];
                            $theta = $lon1 - $lon2;
                            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                            $dist = acos($dist);
                            $dist = rad2deg($dist);
                            $miles = $dist * 60 * 1.1515;
                            $meters = round($miles * 1609.344, 2);
                            // if((float)$meters < (float)$areaDetail->radius){
                                $waktu_masuk_seharusnya = new DateTime($c->in);
                                $waktu_masuk_sebenarnya = new DateTime($data['time']);
                                $selisih = $waktu_masuk_sebenarnya->diff($waktu_masuk_seharusnya);
                                $status = strtotime($data['time']) > strtotime($c->in) ?'late':'unlate';
                                
                                $q->user_id = $data['user_id'];
                                $q->departement_id = $data['departement_id'];
                                $q->position_id = $data['position_id'];
                                $q->level_id = $data['level_id'];
                                $q->att_group_schedule_id = $data['att_group_schedule_id'];
                                $q->area_id = $data['area_id'];
                                $q->location = $data['location'];
                                $q->time = $data['time'];
                                $q->difference = $selisih->format('%H:%I:%S');
                                $q->photo = $data['photo'];
                                $q->status = $status;
                                $q->save();
                                $q->pulang()->create([
                                    "user_id" => $data['user_id'],
                                    "departement_id" => $data['departement_id'],
                                    "position_id" => $data['position_id'],
                                    "level_id" => $data['level_id'],
                                    "att_group_schedule_id" => $data['att_group_schedule_id'],
                                    "area_id" => $data['area_id'],
                                    "location" => $data['location'],
                                    "lat" => null,
                                    "lng" => null,
                                    "time" => null,
                                    "difference" => null,
                                    "photo" => null,
                                    "status" => null,
                                ]);
                                Notification::make()
                                    ->title('Presence successfuly')
                                    ->success()
                                    ->send();
                            // }else{
                            //     Notification::make()
                            //         ->title('The absence radius distance is insufficient, the minimum absence radius distance is '.$areaDetail->radius.' meters.')
                            //         ->danger()
                            //         ->send();
                            // }
                        }else{
                            Notification::make()
                                ->title('Location is not available/invalid location for this user')
                                ->danger()
                                ->send();
                        }
                    }else{
                        Notification::make()
                            ->title("you can't cheat by excluding other people!")
                            ->danger()
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('Date invalid for this presence now!')
                        ->danger()
                        ->send();
                }
            }),

            // absen pulang
            Actions\Action::make('out')
            ->icon("fas-fingerprint")
            ->label("Attendance Out")
            ->color('danger')
            ->outlined()
            ->fillForm(function (): array {
                $v = DB::table('att_group_schedule as ags')
                    ->join('user_att_groups as uag','ags.user_att_group_id','=','uag.id')
                    ->join('user_att_group_relations as uagr','uag.id','=','uagr.user_att_group_id')
                    ->where('uagr.user_id','=', auth()->id())
                    ->where('ags.date_work','=', date('Y-m-d'))
                    ->select('ags.id')
                    ->first();
                return [
                    'user_id' => auth()->id(),
                    'att_group_schedule_id' => $v ? $v->id : null
                ];
            })
            ->form([
                Section::make("Make sure the location coordinates, work schedule, and location where you work are in accordance with what has been determined!")
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
                        ->directory('attendance-out')
                        // ->required()
                        ->columnSpanFull(),
                ])
            ])
            ->mutateFormDataUsing(function (array $data): array {
                $jam_sekarang = Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                $data['time'] = $jam_sekarang;
                $data['departement_id'] = auth()->user()->departemen_id;
                $data['position_id'] = auth()->user()->position_id;
                $data['level_id'] = auth()->user()->level_id;
                return $data;
            })
            ->action(function (array $data): void {
                $c = DB::table('att_group_schedule as ags')
                            ->join('att_times as at','ags.att_time_id','=','at.id')
                            ->where('ags.id','=', $data['att_group_schedule_id'])
                            ->select('at.out', 'ags.date_work')
                            ->first();
                $f = AttendanceIn::where(function($query)use($data){
                    $query
                    ->where('user_id', $data['user_id'])
                    ->where('att_group_schedule_id', $data['att_group_schedule_id']);
                })
                ->first();
                $q = AttendanceIn::find($f->id);
                $tanggal_validasi = new DateTime($c->date_work);
                $tanggal_hari_ini = new DateTime();
                if ($tanggal_validasi->format('Y-m-d') === $tanggal_hari_ini->format('Y-m-d')) {
                    if ((int)$data['user_id'] === auth()->id()) {
                        $validateArea = AttArea::whereHas('user', function ($query) use ($data){
                            $query->where('user_id', $data['user_id']);
                        })
                        ->where('id', $data['area_id']);
                        $area = $validateArea->count('*');
                        $areaDetail = $validateArea->first();
                        if($area > 0){
                            $lat1 = floatval($areaDetail->lat);
                            $lon1 = floatval($areaDetail->lng);
        
                            // Koordinat lokasi
                            $lat2 = $data['location']['lat'];
                            $lon2 = $data['location']['lng'];
                            $theta = $lon1 - $lon2;
                            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                            $dist = acos($dist);
                            $dist = rad2deg($dist);
                            $miles = $dist * 60 * 1.1515;
                            $meters = round($miles * 1609.344, 2);
                            if((float)$meters < (float)$areaDetail->radius){
                                $waktu_masuk_seharusnya = new DateTime($c->out);
                                $waktu_masuk_sebenarnya = new DateTime($data['time']);
                                $selisih = $waktu_masuk_sebenarnya->diff($waktu_masuk_seharusnya);
                                $status = strtotime($data['time']) > strtotime($c->out) ?'late':'unlate';
                                $q->pulang->update([
                                    'area_id'=>$data['area_id'],
                                    'lat'=>$data['location']['lat'],
                                    'lng'=>$data['location']['lng'],
                                    'time'=>$data['time'],
                                    'difference'=>$selisih->format('%H:%I:%S'),
                                    'photo'=>$data['photo'],
                                    'status'=>$status,
                                ]);
                                Notification::make()
                                    ->title('Presence successfuly')
                                    ->success()
                                    ->send();
                            }else{
                                Notification::make()
                                    ->title('The absence radius distance is insufficient, the minimum absence radius distance is '.$areaDetail->radius.' meters.')
                                    ->danger()
                                    ->send();
                            }
                        }else{
                            Notification::make()
                                ->title('Location is not available/invalid location for this user')
                                ->danger()
                                ->send();
                        }
                    }else{
                        Notification::make()
                            ->title("you can't cheat by excluding other people!")
                            ->danger()
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('Date invalid for this presence now!')
                        ->danger()
                        ->send();
                }
            }),
        ];
    }
}
