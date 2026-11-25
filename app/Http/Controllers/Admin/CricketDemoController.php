<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CricketDemoMatch;
use App\Services\Cricket\Demo\DemoMatchSimulator;
use Illuminate\Http\Request;

class CricketDemoController extends Controller
{
    public function index(DemoMatchSimulator $demo)
    {
        return view('admin.cricket.demo', [
            'matches' => CricketDemoMatch::with('balls')->latest('id')->get(),
            'kinds' => $demo->kinds(),
        ]);
    }

    public function store(Request $request, DemoMatchSimulator $demo)
    {
        $data = $request->validate([
            'team1' => ['required', 'string', 'max:120'],
            'team2' => ['required', 'string', 'max:120'],
        ]);

        $demo->create($data['team1'], $data['team2']);

        return back()->with('success', 'Demo match created. It is not a real-money match.');
    }

    public function start(CricketDemoMatch $demoMatch, DemoMatchSimulator $demo)
    {
        $demo->start($demoMatch);

        return back()->with('success', 'Demo started.');
    }

    public function pause(CricketDemoMatch $demoMatch, DemoMatchSimulator $demo)
    {
        $demo->pause($demoMatch);

        return back()->with('success', 'Demo paused. Markets suspend until it resumes.');
    }

    public function resume(CricketDemoMatch $demoMatch, DemoMatchSimulator $demo)
    {
        $demo->resume($demoMatch);

        return back()->with('success', 'Demo resumed.');
    }

    public function auto(CricketDemoMatch $demoMatch)
    {
        $demoMatch->update(['auto_bowl' => ! $demoMatch->auto_bowl]);

        return back()->with('success', $demoMatch->auto_bowl
            ? 'Automatic balls will advance on the cricket scheduler.'
            : 'Automatic balls are off.');
    }

    public function ball(Request $request, CricketDemoMatch $demoMatch, DemoMatchSimulator $demo)
    {
        $data = $request->validate(['kind' => ['required', 'string']]);
        $demo->bowl($demoMatch, $data['kind']);

        return back()->with('success', 'Ball recorded.');
    }
}
