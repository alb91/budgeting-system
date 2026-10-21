<?php 

namespace App\Controllers; 

use App\Services\CategoriesService;
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request; 

class CategoriesController
{
    public function index(Request $request, Response $response): Response
    {
        $categories = CategoriesService::getAll(); 

        return $this->json($response, $categories);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true); 
    
        if(!$data){
            return $this->json($response, [
                'error' => 'Invalid JSON'
            ], 400);
        } 

        try {
            $category = CategoriesService::create($data); 
            return $this->json($response, $category, 201); 
        } catch (\InvalidArgumentException $e) {
            return $this->json($response, [
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id']; 
        $data = json_decode($request->getBody()->getContents(), true);

        if (!$data) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400); 
        }

        try {
            $category = CategoriesService::update($id, $data);
            return $this->json($response, $category, 200);
        } catch (\InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400); 
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        if (!$id) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        try {
            CategoriesService::delete($id);

            return $response->withStatus(204);
        } catch (\InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data)); 
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status); 
    }

}