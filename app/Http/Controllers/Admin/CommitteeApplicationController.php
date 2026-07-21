<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommitteeApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CommitteeApplicationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/CommitteeApplications', [
            'applications' => CommitteeApplication::query()
                ->with(['committee:id,name', 'user:id,name,email,phone,position'])
                ->latest()
                ->get()
                ->map(fn ($application) => [
                    'id' => $application->id,
                    'committee' => $application->committee->name,
                    'name' => $application->user->name,
                    'email' => $application->user->email,
                    'phone' => $application->user->phone,
                    'position' => $application->user->position,
                    'status' => $application->status,
                    'review_note' => $application->review_note,
                ]),
        ]);
    }

    public function verify(Request $request, CommitteeApplication $application): RedirectResponse
    {
        return $this->review($request, $application, 'verified', null, 'Pengajuan Pengurus Daerah diverifikasi.');
    }

    public function revision(Request $request, CommitteeApplication $application): RedirectResponse
    {
        $note = $request->validate(['note' => ['required', 'string', 'max:255']])['note'];

        return $this->review($request, $application, 'revision_required', $note, 'Perbaikan pengajuan diminta.');
    }

    public function reject(Request $request, CommitteeApplication $application): RedirectResponse
    {
        $note = $request->validate(['note' => ['required', 'string', 'max:255']])['note'];

        return $this->review($request, $application, 'rejected', $note, 'Pengajuan Pengurus Daerah ditolak.');
    }

    private function review(Request $request, CommitteeApplication $application, string $status, ?string $note, string $message): RedirectResponse
    {
        abort_unless(in_array($application->status, ['pending', 'revision_required'], true), 422, 'Pengajuan ini sudah selesai diproses.');

        DB::transaction(function () use ($request, $application, $status, $note) {
            $from = $application->status;
            $application->update([
                'status' => $status,
                'review_note' => $note,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'active_committee_id' => $status === 'rejected' ? null : $application->regional_committee_id,
            ]);
            $application->user()->update(['account_status' => $status]);

            DB::table('committee_application_audits')->insert([
                'committee_application_id' => $application->id,
                'actor_id' => $request->user()->id,
                'from_status' => $from,
                'to_status' => $status,
                'note' => $note,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', $message);
    }
}
