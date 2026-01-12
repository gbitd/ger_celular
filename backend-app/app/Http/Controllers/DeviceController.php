<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Repositories\DeviceRepository;

class DeviceController extends Controller
{
    public function index(Request $request, DeviceRepository $repo)
    {
        return response()->json(
            $repo->findByUser(
                $request->user()->id,
                $request->all()
            )
        );
    }

    public function store(StoreDeviceRequest $request, DeviceRepository $repo)
    {
        $id = $repo->create([
            ...$request->validated(),
            'in_use' => false,
            'user_id' => $request->user()->id
        ]);

        return response()->json(['id' => $id], 201);
    }

    public function update(UpdateDeviceRequest $request, $id, DeviceRepository $repo)
    {
        $repo->update($id, $request->user()->id, $request->validated());
        return response()->noContent();
    }

    public function destroy($id, DeviceRepository $repo)
    {
        $repo->softDelete($id, auth()->id());
        return response()->noContent();
    }

    public function toggleUse($id, DeviceRepository $repo)
    {
        $repo->toggleUse($id, auth()->id());
        return response()->noContent();
    }
}

