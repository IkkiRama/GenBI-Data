<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {

            $events = Event::orderBy('tanggal', 'desc')->withoutTrashed()->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'nama' => $event->nama,
                    'slug' => $event->slug,
                    'image' => $event->image,
                    'deskripsi' => $event->deskripsi,
                    'tempat' => $event->tempat,
                    'tanggal' => $event->tanggal,
                    'link_gmap' => $event->link_gmap,
                    'contact_person' => $event->contact_person,
                    'status' => $event->status,
                    'created_at' => $event->created_at,
                    'updated_at' => $event->updated_at,
                ];
            });

            if (empty($events)) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Event Tidak Ditemukan"
                ], 404, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $events,
                "message" => "Event Berhasil Ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Terjadi Kesalahan pada Server: " . $e->getMessage()
            ], 500, $headers);

        }
    }

    /**
     * Display the specified event resource.
     */
    public function show(string $slug)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {

           $event = Event::where('slug', $slug)->with('pemateri')->first();

            if (empty($event)) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Event Tidak Ditemukan"
                ], 404, $headers);
            }

            $eventData = [
                'id' => $event->id,
                'nama' => $event->nama,
                'slug' => $event->slug,
                'image' => $event->image,
                'deskripsi' => $event->deskripsi,
                'tempat' => $event->tempat,
                'tanggal' => $event->tanggal,
                'link_gmap' => $event->link_gmap,
                'contact_person' => $event->contact_person,
                'status' => $event->status,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
                'pemateri' => $event->pemateri,
            ];

            return response()->json([
                "success" => true,
                "data" => $eventData,
                "message" => "Event Berhasil Ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Terjadi Kesalahan pada Server: " . $e->getMessage()
            ], 500, $headers);

        }
    }
}