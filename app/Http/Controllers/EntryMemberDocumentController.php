<?php

namespace App\Http\Controllers;

use App\Models\EntryMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntryMemberDocumentController extends Controller
{
    public function __invoke(Request $request, EntryMember $member, string $document): StreamedResponse
    {
        $entry = $member->eventEntry;
        abort_unless($request->user()->isSuperAdmin() || ($request->user()->isPdAdmin() && $entry?->regional_committee_id === $request->user()->regional_committee_id), 403);

        $path = data_get($member->documents, $document);
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), ['X-Content-Type-Options' => 'nosniff']);
    }
}
