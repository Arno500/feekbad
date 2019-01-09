<?php

namespace App\Http\Controllers;

use App\Feedback;
use App\Tag;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Debugbar;

class FeedbackController extends Controller
{
    public function get(Request $request)
    {
        Debugbar::startMeasure('getFeedbacks','Getting feedbacks');
        $lastrefreshtime = $request->query('lastrefreshtime');
        if (isset($lastrefreshtime)) {
            $refreshedDate = new Carbon;
            $refreshedDate = Carbon::createFromTimestampMs($lastrefreshtime);
            return Feedback::with(["tags"])->where('updated_at', '>=', $refreshedDate)->get();
        } elseif (empty($request->query())) {
            return Feedback::get();
        } else {
            return response()->json([
                "message" => "The format of the query is not quite right :",
                "Query attributes (optional)" => ["lastrefreshtime"],
                "Format" => "in ms since 01-01-1970 (like JavaScript)",
            ], 400);
        }
        Debugbar::stopMeasure('getFeedbacks');
    }

    public function post(Request $request)
    {
        $data = $request->all();
        if (isset($data["name"]) && isset($data["description"]) && isset($data["tags"])) {
            $feedback = new Feedback;
            $feedback->setAttribute('name', $data["name"]);
            $feedback->setAttribute('description', $data["description"]);
            $feedback->setAttribute('author', Auth::user()->getUserInfo()["id"]);

            $feedback->save();

            foreach ($data["tags"] as $tag) {
                $tagInDB = Tag::where('name', $tag)->first();

                if (!isset($tagInDB)) {
                    $createdTag = new Tag;
                    $createdTag->setAttribute('name', $tag);
                    $createdTag->setAttribute('color', "#" . dechex(rand(0x000000, 0xFFFFFF)));
                    $createdTag->save();
                    $tagInDB = $createdTag;
                }
                $feedback->tags()->attach($tagInDB);

            }

        } else {
            return response()->json([
                "message" => "The format of the feeback is not quite right :",
                "Attributes needed" => ["name", "description", "[tags]"],
            ], 400);
        }

        return $feedback;
    }

    public function put(Request $request, $id = null)
    {
        $data = $request->all();
        Debugbar::startMeasure('findingAuth','Getting user data');
        $user = User::find(Auth::user()->getUserInfo()->id);
        Debugbar::stopMeasure('findingAuth');
        $modified = false;

        if ($id) {
            $feedback = Feedback::find($id);
        } else {
            $feedback = new Feedback;
        }

        if (!$id && (!isset($data["name"]) || !isset($data["description"]))) {
            return response("Please send name and description at least, for creation", 400);
        }

        if (isset($data['name']) && $user->admin) {
            $feedback->name = $data['name'];
            $modified = true;
        }
        if (isset($data['description']) && $user->admin) {
            $feedback->description = $data['description'];
            $modified = true;
        }

        if (isset($data['votes'])) {

            Debugbar::startMeasure('settingVotes','Setting new votes count');
            $votesNumber = $data['votes'];
            $myVotes = $feedback->myVotes;

        


            if ($votesNumber <= 3 && $votesNumber >= 0 && ($myVotes <= 9 || $myVotes === null)) {

                if ($myVotes === null) {
                    $feedback->votes()->attach($user->id, ['number' => $votesNumber]);

                } else {
                    $feedback->votes()->updateExistingPivot($user->id, ['number' => $votesNumber]);
                }

                //Max votes logic for users
               /*  if ($user->votes == env("MAX_VOTES", 9)) {
                    $user->firstvote = Carbon::now();
                }
                $user->votes = intval(env("MAX_VOTES", 9) - $user->votes());
                $user->save(); */
                $feedback->touch();

                Debugbar::stopMeasure('settingVotes');


            }
        }
        if (isset($data['done']) && $user->admin) {
            $feedback->done = $data['done'];
            $modified = true;
        }
        if (isset($data["tags"]) && $user->admin) {
            foreach ($data["tags"] as $tag) {
                $tagInDB = Tag::where('name', $tag)->first();

                if (!isset($tagInDB)) {
                    $createdTag = new Tag;
                    $createdTag->setAttribute('name', $tag);
                    $createdTag->setAttribute('color', "#" . dechex(rand(0x000000, 0xFFFFFF)));
                    $createdTag->save();
                    $tagInDB = $createdTag;
                }
                $feedback->tags()->attach($tagInDB);

            }
            $feedback->touch();
        }
        if (!isset($id)) {
            $feedback->setAttribute('author', Auth::user()->getUserInfo()["id"]);
            $modified = true;
        }
        if ($modified) {
            $feedback->save();
        }
    }

    public function delete(Request $request, $id)
    {
        $data = $request->all();
        $user = Auth::user()->getUserInfo();
        if ($user->admin) {
            Feedback::destroy($id);
        }
    }
}
