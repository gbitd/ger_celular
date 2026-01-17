<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Repositories\DeviceRepository;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    private DeviceRepository $devices;

    public function __construct(DeviceRepository $devices)
    {
        $this->devices = $devices;
    }

    public function store(StoreDeviceRequest $request)
    {
        $id = $this->devices->create([
            'name' => $request->name,
            'location' => $request->location,
            'purchase_date' => $request->purchase_date,
            'in_use' => 0,                              //Mysql trata BOOL como TINYINT. 0 = false, 1 = true
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'id' => $id,
        ], 201);
    }

    public function index(Request $request)
    {

        $list = $this->devices->findByUser(
            $request->user()->id,
            $request->only([
                'in_use',
                'location',
                'from',
                'to',
                'page'
            ])
        );

        return response()->json($list);
    }

    public function update(UpdateDeviceRequest $request, int $id)
    {
        $updated = $this->devices->update(
            $id,
            $request->user()->id,
            $request->validated()
        );

        if (! $updated) {
            return response()->json([
                'message' => 'Dispositivo não encontrado'
            ], 404);
        }

        return response()->noContent();
    }

    public function destroy(int $id)
    {
        $deleted = $this->devices->softDelete(
            $id,
            auth()->id()
        );

        if (! $deleted) {
            return response()->json([
                'message' => 'Dispositivo não encontrado'
            ], 404);
        }

        return response()->noContent();
    }

    public function toggleUse(int $id)
    {
        $updated = $this->devices->toggleUse(
            $id,
            auth()->id()
        );

        if (! $updated) {
            return response()->json([
                'message' => 'Dispositivo não encontrado'
            ], 404);
        }

        return response()->noContent();
    }

}
