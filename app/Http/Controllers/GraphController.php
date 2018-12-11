<?php

namespace App\Http\Controllers;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GraphController extends Controller
{
    private $api;
    public function __construct(Facebook $fb)
    {
        $this->middleware(function ($request, $next) use ($fb) {
            $fb->setDefaultAccessToken(Auth::user()->token);
            $this->api = $fb;
            return $next($request);
        });
    }

    public function retrieveUserProfile(){
        try {

           

            //$user = $this->api->get('me?fields=accounts{fan_count,name}')->getGraphUser();
            $user = $this->api->get('me?fields=accounts{fan_count,name}');
            $fb_user = $this->api->get('me?fields=first_name')->getGraphUser();
            //dd($user->getGraphUser()->asArray());
            $u = $user->getGraphUser()->asArray();
            /* for($i = 0; $i <count($u);$i++){
                echo $u['accounts'][$i]['fan_count'];
            }*/
            $facebook_user = new FacebookUSer;
            $facebook_user->user($fb_user);
            $facebook_user->save();
            return view('data')->with('pages',$u);
        } catch (FacebookSDKException $e) {

        }

    }

    public function publishToProfile(Request $request){
        try {
            $response = $this->api->post('/me/feed', [
                'message' => $request->message
            ]);
            dd($response);
        } catch (FacebookSDKException $e) {

        }
    }

    public function publishToPage(){

        $page_id = '257545431025617';

        try {
            $post = $this->api->post('/' . $page_id . '/feed', array('message' => 'New post...'), $this->getPageAccessToken($page_id));

            $post = $post->getGraphNode()->asArray();

            dd($post);

        } catch (FacebookSDKException $e) {
            dd($e); // handle exception
        }
    }

    public function getPageAccessToken($page_id){
        try {
            // Get the \Facebook\GraphNodes\GraphUser object for the current user.
            // If you provided a 'default_access_token', the '{access-token}' is optional.
            $response = $this->api->get('/me/accounts', Auth::user()->token);
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        try {
            $pages = $response->getGraphEdge()->asArray();
            foreach ($pages as $key) {
                if ($key['id'] == $page_id) {
                    return $key['access_token'];
                }
            }
        } catch (FacebookSDKException $e) {
            dd($e); // handle exception
        }
    }
}
