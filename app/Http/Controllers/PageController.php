<?php

namespace Proto\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redirect;
use Proto\Http\Requests;
use Proto\Http\Controllers\Controller;

use Proto\Models\Page;

use Auth;
use Session;

class PageController extends Controller
{
    protected $reservedSlugs = array('add', 'edit', 'delete');

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::all();
        return view("pages.list", ['pages' => $pages]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("pages.edit", ['item' => null, 'new' => true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $page = new Page($request->all());

        if($request->has('is_member_only')) {
            $page->is_member_only = true;
        } else {
            $page->is_member_only = false;
        }

        if(in_array($request->slug, $this->reservedSlugs)) {
            Session::flash('flash_message', 'This URL has been reserved and can\'t be used. Please choose a different URL.');

            return view('pages.edit', ['item' => $page, 'new' => true]);
        }

        if(Page::where('slug', $page->slug)->get()->count() > 0) {
            Session::flash('flash_message', 'This URL has been reserved and can\'t be used. Please choose a different URL.');

            return view('pages.edit', ['item' => $page, 'new' => true]);
        }

        $page->save();

        Session::flash('flash_message', 'Page ' . $page->title . ' has been created.');
        return Redirect::route('page::list');
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $page = Page::where('slug', '=', $slug)->first();

        if($page == null) {
            abort(404, "Page not found.");
        }

        if($page->is_member_only && !(Auth::check() && Auth::user()->member != null)) {
            abort(403, "You need to be a member of S.A. Proto to see this page.");
        }

        return view('pages.show', ['page' => $page]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page = Page::findOrFail($id);

        return view('pages.edit', ['item' => $page, 'new' => false]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        if(($request->slug != $page->slug) && Page::where('slug', $page->slug)->get()->count() > 0) {
            Session::flash('flash_message', 'This URL has been reserved and can\'t be used. Please choose a different URL.');

            return view('pages.edit', ['item' => $request, 'new' => false]);
        }

        $page->fill($request->all());

        if($request->has('is_member_only')) {
            $page->is_member_only = true;
        } else {
            $page->is_member_only = false;
        }

        if(in_array($request->slug, $this->reservedSlugs)) {
            Session::flash('flash_message', 'This URL has been reserved and can\'t be used. Please choose a different URL.');

            return view('pages.edit', ['item' => $page, 'new' => false]);
        }

        $page->save();

        Session::flash('flash_message', 'Page ' . $page->title . ' has been saved.');
        return Redirect::route('page::list');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $page = Page::findOrfail($id);

        Session::flash('flash_message', 'Page ' . $page->title . ' has been removed.');

        $page->delete();

        return Redirect::route('page::list');
    }
}
