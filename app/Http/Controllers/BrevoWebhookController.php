<?php

namespace App\Http\Controllers;

use App\Models\EmailDispatchRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrevoWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            $event = $request->input('event');
            $messageId = $request->input('message-id');
            $email = $request->input('email');
            $timestamp = now();
            $link = $request->input('link');

            error_log($email);

            // Find the recipient by message ID
            $recipient = EmailDispatchRecipient::where('email', $email)
                ->orWhere('message_id', $messageId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$recipient) {
                Log::warning('Brevo webhook: Recipient not found', [
                    'message_id' => $messageId,
                    'email' => $email,
                    'event' => $event
                ]);
                return response()->json(['status' => 'warning', 'message' => 'Recipient not found']);
            }

            switch ($event) {
                case 'delivered':
                    $recipient->update([
                        'message_id' => $messageId,
                        'status' => 'delivered',
                        'delivered_at' => $timestamp
                    ]);
                    break;

                case 'opened':
                    if ($recipient->status === 'delivered') {
                        $recipient->update([
                            'status' => 'opened',
                            'opened_at' => $timestamp
                        ]);
                    }
                    break;

                case 'click':
                    if ($recipient->status === 'delivered' || $recipient->status === 'opened') {
                        $recipient->update([
                            'status' => 'opened',
                            'opened_at' => $recipient->opened_at ?? $timestamp,
                            'clicked_at' => $timestamp,
                            'clicked_link' => $link
                        ]);
                    }
                    break;

                case 'bounce':
                case 'blocked':
                case 'spam':
                    $recipient->update([
                        'status' => 'failed',
                        'error' => "Email {$event}: " . $request->input('reason', 'No reason provided')
                    ]);
                    break;

                default:
                    Log::info('Unhandled Brevo event', [
                        'event' => $event,
                        'message_id' => $messageId,
                        'email' => $email
                    ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Failed to process Brevo webhook', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process webhook'
            ], 500);
        }
    }
} 