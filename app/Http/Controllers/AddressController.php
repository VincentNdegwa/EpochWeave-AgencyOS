<?php

namespace App\Http\Controllers;

use App\Exceptions\AddressException;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addressService) {}

    public function index(Request $request)
    {
        $type = $request->query('addressable_type');
        $id = $request->query('addressable_id');

        if (! $type || ! $id) {
            abort(400);
        }

        $addresses = $this->addressService->listForEntity($type, (int) $id);

        return Inertia::render('address/index', [
            'addresses' => $addresses,
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        try {
            $this->addressService->createAddress($request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Address created successfully.']);

            return redirect()->back();
        } catch (AddressException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        try {
            $this->addressService->updateAddress($address, $request->validated());
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Address updated successfully.']);

            return redirect()->back();
        } catch (AddressException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Address $address): RedirectResponse
    {
        try {
            $this->addressService->deleteAddress($address);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Address deleted successfully.']);

            return redirect()->back();
        } catch (AddressException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }

    public function setPrimary(Request $request, Address $address): RedirectResponse
    {
        try {
            $this->addressService->setPrimary($address);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Address set as primary successfully.']);

            return redirect()->back();
        } catch (AddressException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return redirect()->back();
        }
    }
}
