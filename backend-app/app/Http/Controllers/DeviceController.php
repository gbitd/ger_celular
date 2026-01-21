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
            'name' => $request->name,
            'location' => $request->location,
            'purchase_date' => $request->purchase_date,
            'in_use' => 0,                              //Mysql trata BOOL como TINYINT. 0 = false, 1 = true
            'user_id' => $request->user()->id,
        ], 201);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $filters = $request->only([
            'in_use',
            'location',
            'from',
            'to',
            'page'
        ]);
        // Paginação. Hard coded por agora para manter a especificação do projeto,
        // mas seria interessante que o limit e offset fossem recebidos por query strings na api também.
        $filters['per_page'] = 10;

        $filteredList = $this->devices->findByUserFiltered($userId, $filters);
        $countTotal = $this->devices->countTotalByUser($userId);

        $meta = [
            'current_page' => (int)$filters['page'],
            'last_page' => ceil($countTotal / $filters['per_page']),
            'per_page' => $filters['per_page'],
            'total' => $countTotal
        ];

        if ($countTotal > 0 && $meta['current_page'] > $meta['last_page']){
            return response()->json([
                'message' => 'Página inválida'
            ], 400);
        }
        return response()->json([
            'data' => $filteredList,
            'meta' => $meta]);
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

        $updatedDevice = $this->devices->findById(
            $id,
            auth()->id()
        );

        if (! $updated || ! $updatedDevice) {
            return response()->json([
                'message' => 'Dispositivo não encontrado'
            ], 404);
        }

        return response()->json($updatedDevice);
    }

}
