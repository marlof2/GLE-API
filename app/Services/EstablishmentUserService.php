<?php

namespace App\Services;

use App\Models\Establishment;
use Illuminate\Http\Response;
use App\Models\EstablishmentUser;

class EstablishmentUserService
{
    protected $establishment_user;
    protected $pageLimit;

    public function __construct(EstablishmentUser $establishment_user)
    {
        $this->establishment_user = $establishment_user;
        $this->pageLimit = 10;
    }
    public function index($request)
    {
        $data = $this->establishment_user->with("establishment_user");
        if ($request->filled('search')) {
            return response()->json($this->establishment_user::Filtro($request->search));
        }
        if ($request->filled('limit')) {
            $data = ["data" => $this->establishment_user->get()];
            return response()->json($data, Response::HTTP_OK);
        } else {
            $page_limit = $request->filled('per_page') ? $request->per_page : config($this->pageLimit);
            $data = $data->paginate($page_limit);
        }
        return response()->json($data, Response::HTTP_OK);
    }
    public function store($request)
    {
        try {
            $user_id = $request["user_id"];
            $establishment_id = $request->establishment_id;

            foreach ($user_id as $key => $id) {

                $this->establishment_user->create(["establishment_id" => $establishment_id, "user_id" => $id]);
            }

            return response()->json(["message" => "Profissionais vinculados com sucesso."], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(["message" => 'Não foi possível cadastrar', "error" => $e], Response::HTTP_NOT_ACCEPTABLE);
        }
    }
    public function show($user_id)
    {
        $data = $this->establishment_user->with("establishment:id,name,cnpj,cpf,phone")->where('user_id', $user_id)->get();
        if (!$data) {
            return response()->json(['error' => 'Dados não encontrados'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(["data" => $data], Response::HTTP_OK);
    }
    public function update($request, $id)
    {
        $data = $this->establishment_user->find($id);
        if (!$data) {
            return response()->json(['error' => 'Dados não encontrados'], Response::HTTP_NOT_FOUND);
        }
        $dataFrom = $request->all();
        try {
            $data->update($dataFrom);
            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(["message" => 'Não foi possível atualizar', "error" => $e], Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    public function destroy($request)
    {
        try {
            $user_id = $request["user_id"];
            $establishment_id = $request->establishment_id;

            $establishment = $this->establishment_user->where('establishment_id', $establishment_id)->first();

            if (!$establishment) {
                return response()->json([
                    "message" => "O estabelecimento informado não existe."
                ], Response::HTTP_NOT_FOUND);
            }

            foreach ($user_id as $key => $id) {
                $this->establishment_user->where([
                    'establishment_id' => $establishment_id,
                    'user_id' => $id
                ])->delete();
            }

            return response()->json(["message" => "Profissionais desvinculados com sucesso."], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(["message" => 'Não foi possível desvincular', "error" => $e], Response::HTTP_NOT_ACCEPTABLE);
        }
    }
}