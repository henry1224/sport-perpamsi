<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CommitteeApplication;
use App\Models\RegionalCommittee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CommitteeRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'committees' => RegionalCommittee::query()
                ->whereNotIn('id', CommitteeApplication::query()->whereNotNull('active_committee_id')->select('active_committee_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'regional_committee_id' => [
                'required',
                'integer',
                Rule::exists('regional_committees', 'id'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (CommitteeApplication::query()->where('active_committee_id', $value)->exists()) {
                        $fail('PD PERPAMSI ini sudah memiliki pengajuan aktif.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:120'],
            'position' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $committee = RegionalCommittee::query()->lockForUpdate()->findOrFail($data['regional_committee_id']);

            if (CommitteeApplication::query()->where('active_committee_id', $committee->id)->exists()) {
                abort(409, 'PD PERPAMSI ini sudah memiliki pengajuan aktif.');
            }

            $user = User::query()->create([
                ...$data,
                'role' => 'pd_admin',
                'account_status' => 'pending',
            ]);

            $application = CommitteeApplication::query()->create([
                'regional_committee_id' => $committee->id,
                'active_committee_id' => $committee->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            DB::table('committee_application_audits')->insert([
                'committee_application_id' => $application->id,
                'actor_id' => $user->id,
                'from_status' => null,
                'to_status' => 'pending',
                'note' => 'Pengajuan dibuat.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('registration.status')->with('success', 'Pengajuan berhasil dikirim.');
    }

    public function status(Request $request): Response
    {
        $application = CommitteeApplication::query()
            ->with('committee:id,name')
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return Inertia::render('Auth/RegistrationStatus', [
            'application' => [
                'committee' => $application->committee->name,
                'status' => $application->status,
                'review_note' => $application->review_note,
                'updated_at' => $application->updated_at->format('d M Y H:i'),
            ],
            'applicant' => [
                'name' => $request->user()->name,
                'position' => $request->user()->position,
                'phone' => $request->user()->phone,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $application = CommitteeApplication::query()->where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($application->status === 'revision_required', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'position' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($request, $application, $data) {
            $application->user()->update($data);
            $application->update(['status' => 'pending', 'review_note' => null, 'reviewed_by' => null, 'reviewed_at' => null]);
            $application->user()->update(['account_status' => 'pending']);
            DB::table('committee_application_audits')->insert([
                'committee_application_id' => $application->id,
                'actor_id' => $request->user()->id,
                'from_status' => 'revision_required',
                'to_status' => 'pending',
                'note' => 'Perbaikan dikirim ulang.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Perbaikan berhasil dikirim ulang.');
    }
}
