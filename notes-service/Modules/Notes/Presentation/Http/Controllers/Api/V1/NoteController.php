<?php

namespace Modules\Notes\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// use Modules\Notes\Jobs\ProcessNoteAnalytics;
// use Modules\Notes\Services\NoteService;
use Modules\Notes\Application\CommandHandlers\CreateNoteHandler;
use Modules\Notes\Application\CommandHandlers\DeleteNoteHandler;
use Modules\Notes\Application\CommandHandlers\UpdateNoteHandler;
use Modules\Notes\Application\Commands\CreateNoteCommand;
use Modules\Notes\Application\Commands\DeleteNoteCommand;
use Modules\Notes\Application\Commands\UpdateNoteCommand;
use Modules\Notes\Application\Contracts\NoteServiceInterface;
use Modules\Notes\Application\DTOs\CreateNoteDTO;
use Modules\Notes\Application\DTOs\UpdateNoteDTO;
use Modules\Notes\Application\Queries\GetNoteQuery;
use Modules\Notes\Application\Queries\GetUserNotesQuery;
use Modules\Notes\Application\QueryHandlers\GetNoteHandler;
use Modules\Notes\Application\QueryHandlers\GetUsersNoteHandler;
use Modules\Notes\Presentation\Http\Controllers\Controller;
use Modules\Notes\Presentation\Http\Requests\StoreNoteRequest;
use Modules\Notes\Presentation\Http\Requests\UpdateNoteRequest;
use Modules\Notes\Presentation\Transformers\NoteResource;
use Modules\Shared\Responses\ApiResponse;

class NoteController extends Controller
{
    /*
       Not needed again after CORS
    */
    // public $noteService;

    // public function __construct(NoteServiceInterface $noteService){

    //     $this->noteService = $noteService;
    // }

    // LIST (cached + paginated)
    public function index(Request $request, GetUsersNoteHandler $handler)
    {

        $user = $request->attributes->get('user');
        $paginator_query = new GetUserNotesQuery(
            $user->id,
            $request->get('page', 1)
        );

        $result = $handler->handle($paginator_query);

        return ApiResponse::success(
            $result['items'],
            $result['pagination']
        );

    }

    // CREATE
    public function store(StoreNoteRequest $request, CreateNoteHandler $handler)
    {
        $user = $request->attributes->get('user');
        $dto = CreateNoteDTO::fromArray(
            $request->validated(),
            $user->id,
            $request->file('image')
        );

        // WRITE SIDE (COMMAND)  : splitting Noteservice
        $newdto = new CreateNoteCommand(
            $dto->title,
            $dto->content,
            $dto->userId,
            $dto->image,
            $request->header('X-Correlation-ID')
        );

        $note = $handler->handle($newdto);

        Log::info('note.created', [
            'service' => 'notes',
            'note_id' => $note->id,
            'user_id' => $note->user_id,
            'correlation_id' => request()->header('X-Correlation-ID'),
        ]);

        return ApiResponse::create(
            new NoteResource($note)
        );

    }

    public function update(UpdateNoteRequest $request, int $noteId, UpdateNoteHandler $handler)
    {

        $user = $request->attributes->get('user');
        $dto = UpdateNoteDTO::fromArray(
            $request->validated(),
            $user->id,
            $noteId,
            $request->file('image')
        );

        $updatedto = new UpdateNoteCommand(
            $dto->title,
            $dto->content,
            $dto->userId,
            $dto->noteId,
            $dto->image
        );

        $note = $handler->handle($updatedto);

        return ApiResponse::update(
            new NoteResource($note)
        );

    }

    // SHOW (cached)
    public function show(Request $request, $id, GetNoteHandler $handler)
    {
        $user = $request->attributes->get('user');
        $newshow = new GetNoteQuery(
            $id,
            $user->id
        );

        $note = $handler->handle($newshow);

        return ApiResponse::show(
            new NoteResource($note)
        );
    }

    // DELETE
    public function destroy(Request $request, $id, DeleteNoteHandler $handler)
    {

        $user = $request->attributes->get('user');
        $deleteNote = new DeleteNoteCommand(
            $id,
            $user->id
        );

        $note = $handler->handle($deleteNote);

        return ApiResponse::delete(
            []
        );
    }

    public function metrics()
    {
        return response('
            # HELP app_up
            # TYPE app_up gauge
            app_up 2
        ', 200)->header('Content-Type', 'text/plain');

    }
}
