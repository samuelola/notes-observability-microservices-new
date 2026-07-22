<?php

namespace Modules\Shared\Responses;

class ApiResponse
{
    public static function success($data, $pagination = null)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'pagination' => $pagination,
        ], 200);
    }

    public static function create($data)
    {

        return response()->json([
            'status' => 'success',
            'message' => 'Note created successfully',
            'data' => $data,
        ], 201);
    }

    public static function show($data)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Note Retrieved successfully',
            'data' => $data,
        ], 200);
    }

    public static function update($data)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Note Updated successfully',
            'data' => $data,
        ], 200);
    }

    public static function delete($data = [])
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Note Deleted successfully',
            'data' => $data,
        ], 200);
    }
}
