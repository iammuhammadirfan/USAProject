use Illuminate\Support\Facades\Redis;

public function generateTicket(Request $request)
{
    // Attempt to acquire the lock (wait max 5 seconds if locked)
    $lock = Redis::lock('ticket_generation_lock', 10);

    try {
        if ($lock->get()) {
            // Critical section - Only one process can execute this at a time
            DB::beginTransaction();

            $latestTicket = DB::table('tickets')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $ticketNumber = $latestTicket ? $latestTicket->ticket_number + 1 : 1;

            $ticketId = DB::table('tickets')->insertGetId([
                'user_id' => $request->userId,
                'ticket_number' => $ticketNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'ticket_id' => $ticketId,
            ]);
        } else {
            return response()->json([
                'status' => 429,
                'message' => 'Could not acquire lock. Try again later.',
            ], 429);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 500,
            'message' => 'Error generating ticket.',
        ], 500);
    } finally {
        // Always release the lock
        $lock->release();
    }
}