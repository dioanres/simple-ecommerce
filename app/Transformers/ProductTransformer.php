<?php

namespace App\Transformers;

use App\Models\Product;
use Flugg\Responder\Transformers\Transformer;

class ProductTransformer extends Transformer
{
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * List of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Transform the model.
     *
     * @param  \App\Product $product
     * @return array
     */
    public function transform(Product $product)
    {
        return [
            'id'    => (int) $product->id,
            'name'  => $product->name,
            'desc'  => $product->desc,
            'gift'  => $product->gift,
            'price' => $product->price,
            'rating'=> $product->rate,
        ];
    }

    public function collection_transform($products)
    {
        $products = collect($products);
        $return = $products->map(function ($item) {
            return $this->transform($item);
        });
        
        $sort = $return->sortBy([
            ['rating', 'desc'],
            ['id', 'desc'], 
        ]);

        return $sort->values()->all();
    }
}
