<?php 

namespace App\Controllers; 

use App\Services\ExpenseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request; 

class ExpenseController
{
    public function getAllByCycle(Request $request, Response $response, array $args): Response
    {
        $cycleId = (int) $args['id']; 

        if (!$cycleId) {
            return $this->json($response, ['error' => 'Cycle ID required'], 400); 
        }

        try {
            $expenses = ExpenseService::getAllByCycle($cycleId);
            return $this->json($response, $expenses, 200);  
        } catch (\InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400); 
        }
    }

    public function store(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true); 
        
        if(!$data) {
            return $this->json($response, [
                'error' => 'Invalid JSON'
            ], 400);
        }

        try {
            $expense = ExpenseService::create($data); 
            return $this->json($response, $expense, 201); 
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
            $expense = ExpenseService::update($id, $data); 
            return $this->json($response, $expense, 200); 
        } catch (\InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400); 
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id']; 

        try {
            ExpenseService::delete($id); 

            return $response->withStatus(204); 
        } catch (\InvalidArgumentException $e) {
            return $this->json($response, [
                
            ]);
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