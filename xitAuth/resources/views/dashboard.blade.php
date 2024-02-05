<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @role('user')
                        @if (auth()->user()->isCanceled)
                            <p class="text-danger">Your registration is rejected by admin.</p>
                        @else
                            @if (auth()->user()->isVerified)
                                You are successfully logged in !
                            @else
                                Your registration is pending approval from an ADMIN. <br>
                                Please be patient.We will notify you.
                            @endif
                        @endif
                    @else
                        <p>Logged in as ADMIN .</p>
                    @endrole

                </div>
            </div>
        </div>
        @role('admin')
            <div class="card  container mx-auto w-50 mt-5">
                <div class="card-header">Pending User list</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">First</th>
                                <th scope="col">Last</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($data) != 0)
                                @foreach ($data as $d)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $d->name }}</td>
                                        <td>{{ $d->email }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('profile.approve', $d->id) }}"
                                                    class="btn btn-success">Approve</a>
                                                <a href="{{ route('profile.cancel', $d->id) }}"
                                                    class="btn btn-danger">Cancel</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <td colspan="4" class="text-center fw-bold">There is no pending requests</td>
                            @endif



                        </tbody>
                    </table>
                </div>
            </div>
        @endrole


    </div>
</x-app-layout>
