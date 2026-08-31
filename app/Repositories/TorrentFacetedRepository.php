<?php


namespace App\Repositories;

use App\Models\Category;


class TorrentFacetedRepository
{
    /**
     * Return a collection of Category Name from storage.
     *
     * @return \Illuminate\Support\Collection
     */
    public function categories()
    {
        return Category::all()->sortBy('position')->pluck('name', 'id');
    }

    /**
     * Return a collection of Type Name from storage.
     *
     * @return \Illuminate\Support\Collection
     */


    /**
     * Options for sort the search result.
     *
     * @return array
     */
    public function sorting()
    {
        return [
            'created_at'      => trans('torrent.date'),
            'name'            => trans('torrent.name'),
            'seeders'         => trans('torrent.seeders'),
            'leechers'        => trans('torrent.leechers'),
            'times_completed' => trans('torrent.completed-times'),
            'size'            => trans('torrent.size'),
        ];
    }

    /**
     * Options for sort the search result by direction.
     *
     * @return array
     */
    public function direction()
    {
        return [
            'desc' => trans('common.descending'),
            'asc'  => trans('common.ascending'),
        ];
    }
}
