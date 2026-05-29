<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Validate a promotion code for a given course and amount.
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'course_id' => 'nullable|integer|exists:courses,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $promotion = Promotion::where('code', $code)->first();

        if (!$promotion) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid promotion code.',
            ], 422);
        }

        if (!$promotion->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'This promotion is not active.',
            ], 422);
        }

        if ($promotion->isExpired()) {
            return response()->json([
                'valid' => false,
                'message' => 'This promotion has expired.',
            ], 422);
        }

        if ($promotion->isUpcoming()) {
            return response()->json([
                'valid' => false,
                'message' => 'This promotion has not started yet.',
            ], 422);
        }

        if ($promotion->max_uses !== null && $promotion->used_count >= $promotion->max_uses) {
            return response()->json([
                'valid' => false,
                'message' => 'This promotion has reached its usage limit.',
            ], 422);
        }

        if ($request->course_id && !$promotion->appliesToCourse($request->course_id)) {
            return response()->json([
                'valid' => false,
                'message' => 'This promotion does not apply to the selected course.',
            ], 422);
        }

        $discount = $promotion->calculateDiscount((float) $request->amount);

        if ($discount <= 0) {
            return response()->json([
                'valid' => false,
                'message' => 'This promotion cannot be applied to this order amount.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'promotion' => [
                'id' => $promotion->id,
                'code' => $promotion->code,
                'name' => $promotion->name,
                'discount_type' => $promotion->discount_type,
                'discount_value' => $promotion->discount_value,
                'formatted_discount' => $promotion->formattedDiscount(),
            ],
            'discount' => $discount,
            'new_total' => max(0, (float) $request->amount - $discount),
        ]);
    }
}
