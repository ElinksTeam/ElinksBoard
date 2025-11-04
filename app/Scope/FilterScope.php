<?php

namespace App\Scope;

use Illuminate\Database\Eloquent\Builder;

trait FilterScope
{
    public function scopeSetFilterAllowKeys($builder, ...$allowKeys)
    {
        $allowKeys = implode(',', $allowKeys);
        if (!$allowKeys) return $builder;
        $request = request();
        $request->validate([
            'filter.*.key' => "required|in:{$allowKeys}",
            'filter.*.condition' => 'required|in:in,is,not,like,lt,gt',
            'filter.*.value' => 'required'
        ]);
        $filters = $request->input('filter');
        if ($filters) {
            foreach ($filters as $filterConfig) {
                if ($filterConfig['condition'] === 'in') {
                    $builder->whereIn($filterConfig['key'], $filterConfig['value']);
                    continue;
                }
                if ($filterConfig['condition'] === 'is') {
                    $builder->where($filterConfig['key'], $filterConfig['value']);
                    continue;
                }
                if ($filterConfig['condition'] === 'not') {
                    $builder->where($filterConfig['key'], '!=', $filterConfig['value']);
                    continue;
                }
                if ($filterConfig['condition'] === 'gt') {
                    $builder->where($filterConfig['key'], '>', $filterConfig['value']);
                    continue;
                }
                if ($filterConfig['condition'] === 'lt') {
                    $builder->where($filterConfig['key'], '<', $filterConfig['value']);
                    continue;
                }
                if ($filterConfig['condition'] === 'like') {
                    $builder->where($filterConfig['key'], 'like', "%{$filterConfig['value']}%");
                    continue;
                }
            }
        }
        return $builder;
    }
}