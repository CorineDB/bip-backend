<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\categories_document\StoreCategorieDocumentRequest;
use App\Http\Requests\categories_document\UpdateCategorieDocumentRequest;
use App\Services\Contracts\CategorieDocumentServiceInterface;
use Illuminate\Http\JsonResponse;

class CategorieDocumentController extends Controller
{
    protected CategorieDocumentServiceInterface $service;

    public function __construct(CategorieDocumentServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/categories-document",
     *     summary="Liste des categories de canevas standardise",
     *     tags={"CategoriesDocument"},
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    /**
     * @OA\Get(
     *     path="/api/categories-document/{id}",
     *     operationId="getPostById",
     *     tags={"Categories document"},
     *     summary="Get une categorie de document par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categorie de document non retrouve",
     *         @OA\JsonContent(ref="#/components/schemas/CategorieDocument")
     *     ),
     *     @OA\Response(response=404, description="Categorie de document non trouve")
     * )
     */
    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    /**
     * @OA\Post(
     *     path="/api/categories-document",
     *     operationId="storeCategorieDocument",
     *     tags={"Categories document"},
     *     summary="Creer une nouvelle categorie de canevas standardise",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string", example="My first post"),
     *             @OA\Property(property="content", type="string", example="This is the content.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categorie de document creer",
     *         @OA\JsonContent(ref="#/components/schemas/CategorieDocument")
     *     )
     * )
     */
    public function store(StoreCategorieDocumentRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCategorieDocumentRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }


    /**
     * @OA\Delete(
     *     path="/api/categories-document/{id}",
     *     operationId="deleteCategorieDocument",
     *     tags={"Categories Documents"},
     *     summary="Delete a post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Post deleted"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}