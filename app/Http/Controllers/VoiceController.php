<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParseVoiceRequest;
use App\Http\Requests\TransactionRequest;
use App\Http\Requests\VoiceNoteRequest;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\VoiceNote;
use App\Services\FinanceService;
use App\Services\NotificationService;
use App\Services\VoiceParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VoiceController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('voice.index', [
            'categories' => $user->categories()->get(),
            'accounts' => $user->accounts()->get(),
            'defaultAccount' => $user->accounts()->where('is_default', true)->first() ?? $user->accounts()->first(),
            'recentVoice' => $user->transactions()->with(['category', 'account', 'voiceNote'])->where('source', 'voice')->orderByDesc('transaction_date')->limit(6)->get(),
        ]);
    }

    public function parse(ParseVoiceRequest $request): JsonResponse
    {
        $transcript = $request->input('transcript');

        $result = (new VoiceParser)->parse($transcript);

        $categories = Auth::user()->categories()->where('type', $result['type'])->orderBy('name')->get(['id', 'name', 'icon', 'color', 'type']);

        $category = $categories->firstWhere('name', $result['category']);

        return response()->json([
            'ok' => true,
            'parsed' => $result,
            'categories' => $categories,
            'category_id' => $category->id ?? null,
            'accounts' => Auth::user()->accounts()->get(['id', 'name', 'type', 'icon', 'color']),
            'default_account_id' => (Auth::user()->accounts()->where('is_default', true)->first() ?? Auth::user()->accounts()->first())?->id,
        ]);
    }

    public function store(TransactionRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['source'] = 'voice';

        if ($data['amount'] <= 0) {
            return response()->json(['ok' => false, 'message' => 'Nominal transaksi tidak valid.'], 422);
        }

        $transaction = Transaction::create($data);

        (new FinanceService)->applyTransaction($transaction);
        if ($transaction->type === 'expense') {
            (new NotificationService)->checkBudgets(Auth::user(), $transaction->category_id);
        }

        if ($request->hasFile('audio')) {
            $validated = validator($request->only(['audio', 'transcription', 'duration']), [
                'audio' => ['required', 'file', 'max:20000'],
                'transcription' => ['nullable', 'string', 'max:4000'],
                'duration' => ['nullable', 'integer', 'max:7200'],
            ])->validate();

            $this->attachAudio($transaction, $validated['audio'], $validated['transcription'] ?? null, $validated['duration'] ?? 0);
        } elseif ($request->filled('transcription')) {
            VoiceNote::create([
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'transcription' => $request->input('transcription'),
                'duration' => (int) ($request->input('duration') ?? 0),
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Transaksi berhasil dicatat dari suara.',
            'transaction_id' => $transaction->id,
        ]);
    }

    public function uploadAudio(VoiceNoteRequest $request): JsonResponse
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($request->input('transaction_id'));

        $voiceNote = $this->attachAudio(
            $transaction,
            $request->file('audio'),
            $request->input('transcription'),
            (int) ($request->input('duration') ?? 0)
        );

        return response()->json([
            'ok' => true,
            'voice_note' => [
                'id' => $voiceNote->id,
                'duration' => $voiceNote->duration,
                'url' => route('voice.audio', $voiceNote),
                'has_audio' => (bool) $voiceNote->audio_path,
            ],
        ]);
    }

    private function attachAudio(Transaction $transaction, $file, ?string $transcription, int $duration): VoiceNote
    {
        $note = new VoiceNote();
        $note->transaction_id = $transaction->id;
        $note->user_id = $transaction->user_id;
        $note->transcription = $transcription;
        $note->duration = $duration;

        if ($file) {
            $note->audio_path = $file->store('voice-notes', 'local');
            $note->mime_type = $file->getClientMimeType() ?: 'audio/webm';
        }

        $note->save();

        return $note;
    }

    public function streamAudio(VoiceNote $voiceNote): StreamedResponse
    {
        $this->authorize('view', $voiceNote);

        if (! $voiceNote->audio_path || ! Storage::disk('local')->exists($voiceNote->audio_path)) {
            abort(404, 'Audio tidak ditemukan.');
        }

        $path = Storage::disk('local')->path($voiceNote->audio_path);

        return response()->streamDownload(function () use ($path) {
            $stream = fopen($path, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 'voice-' . $voiceNote->id . '.webm', [
            'Content-Type' => $voiceNote->mime_type ?: 'audio/webm',
            'Content-Length' => filesize($path),
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="voice.webm"',
        ]);
    }

    public function deleteAudio(VoiceNote $voiceNote): JsonResponse
    {
        $this->authorize('delete', $voiceNote);

        $voiceNote->delete();

        return response()->json(['ok' => true, 'message' => 'Rekaman suara dihapus.']);
    }
}