<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseInstruction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class InstructionController extends Controller
{
    public function getExerciseInstructions($exerciseId): JsonResponse
    {
        $exercise = Exercise::find($exerciseId);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        $instructions = ExerciseInstruction::where('exercise_id', $exerciseId)
            ->orderBy('instruction_type')
            ->orderBy('step_order')
            ->get()
            ->groupBy('instruction_type');

        return response()->json([
            'success' => true,
            'data' => [
                'exercise' => $exercise->only(['exercise_id', 'exercise_name']),
                'instructions' => $instructions
            ]
        ]);
    }

    public function addExerciseInstructions(Request $request, $exerciseId): JsonResponse
    {
        $exercise = Exercise::find($exerciseId);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        $validated = $request->validate([
            'instructions' => 'required|array|min:1',
            'instructions.*.instruction_type' => [
                'required',
                Rule::in(['setup', 'execution', 'breathing', 'modification', 'common_mistakes'])
            ],
            'instructions.*.instruction_text' => 'required|string',
            'instructions.*.step_order' => 'nullable|integer|min:1',
            'instructions.*.is_critical' => 'boolean'
        ]);

        $createdInstructions = [];

        foreach ($validated['instructions'] as $instructionData) {
            $instructionData['exercise_id'] = $exerciseId;
            $instruction = ExerciseInstruction::create($instructionData);
            $createdInstructions[] = $instruction;
        }

        return response()->json([
            'success' => true,
            'message' => 'Instructions added successfully',
            'data' => $createdInstructions
        ], 201);
    }

    public function updateInstruction(Request $request, $instructionId): JsonResponse
    {
        $instruction = ExerciseInstruction::find($instructionId);

        if (!$instruction) {
            return response()->json([
                'success' => false,
                'message' => 'Instruction not found'
            ], 404);
        }

        $validated = $request->validate([
            'instruction_type' => [
                'nullable',
                Rule::in(['setup', 'execution', 'breathing', 'modification', 'common_mistakes'])
            ],
            'instruction_text' => 'nullable|string',
            'step_order' => 'nullable|integer|min:1',
            'is_critical' => 'nullable|boolean'
        ]);

        $instruction->update(array_filter($validated));

        return response()->json([
            'success' => true,
            'message' => 'Instruction updated successfully',
            'data' => $instruction->fresh()
        ]);
    }

    public function deleteInstruction($instructionId): JsonResponse
    {
        $instruction = ExerciseInstruction::find($instructionId);

        if (!$instruction) {
            return response()->json([
                'success' => false,
                'message' => 'Instruction not found'
            ], 404);
        }

        $instruction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Instruction deleted successfully'
        ]);
    }
}
