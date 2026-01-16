<?php
namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductContoller extends Controller
{
    public function __construct(private readonly ProductService $service)
    {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search']);
        $perPage = (int) $request->get('per_page', 15);

        return $this->service->getAll($filters, $perPage);
    }

    public function store(CreateProductRequest $request): ProductResource
    {
        $validated = $request->validated();

        if ($request->hasFile('images')) {
            $validated['images'] = $request->file('images');
        }

        return $this->service->create($validated);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $validated = $request->validated();

        if ($request->hasFile('images')) {
            $files               = $request->file('images');
            $validated['images'] = is_array($files) ? $files : [$files];
        }

        return $this->service->update($product, $validated);
    }
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('images'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'تم حذف المنتج بنجاح']);
    }

    public function logs(Product $product)
    {
        return \App\Http\Resources\LogResource::collection($product->logs);
    }
}
