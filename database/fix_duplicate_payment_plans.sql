-- ============================================================================
-- Fix Duplicate Enrollment Payment Plans
-- 
-- Problem: enrollment_payment_plans has duplicate enrollment_ids,
--          which blocks the UNIQUE constraint and confuses the payment trigger.
-- 
-- Root Cause: Enrollment creation race condition created extra/wrong plans.
-- 
-- Strategy:
--   1. For each duplicate enrollment_id, keep the plan with the most payments
--      (highest total_paid, or if tied, the one with the most recent updated_at)
--   2. Update any payments referencing the deleted plans to point to the keeper
--   3. Delete the duplicate plans
--   4. Add the UNIQUE constraint
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- Step 1: View duplicates (run this first to inspect)
-- ============================================================================
/*
SELECT enrollment_id, COUNT(*) as cnt, GROUP_CONCAT(id ORDER BY id) as plan_ids
FROM enrollment_payment_plans
GROUP BY enrollment_id
HAVING COUNT(*) > 1;
*/

-- ============================================================================
-- Step 2: Create a temp table identifying which plan to KEEP per enrollment
-- ============================================================================
DROP TEMPORARY TABLE IF EXISTS _plans_to_keep;

CREATE TEMPORARY TABLE _plans_to_keep AS
SELECT epp.enrollment_id,
       -- Pick the keeper: most total_paid, then most recent updated_at, then lowest id
       SUBSTRING_INDEX(GROUP_CONCAT(epp.id ORDER BY epp.total_paid DESC, epp.updated_at DESC, epp.id ASC SEPARATOR ','), ',', 1) + 0 AS keeper_plan_id,
       GROUP_CONCAT(epp.id ORDER BY epp.id ASC SEPARATOR ',') AS all_plan_ids,
       COUNT(*) AS duplicate_count
FROM enrollment_payment_plans epp
GROUP BY epp.enrollment_id
HAVING COUNT(*) > 1;

-- ============================================================================
-- Step 3: Update payments to point to the keeper plan before deleting duplicates
-- ============================================================================
UPDATE payments p
JOIN enrollment_payment_plans dup ON p.payment_plan_id = dup.id
JOIN _plans_to_keep k ON dup.enrollment_id = k.enrollment_id
SET p.payment_plan_id = k.keeper_plan_id
WHERE dup.id != k.keeper_plan_id;

-- ============================================================================
-- Step 4: Delete the duplicate plans (everything except the keeper)
-- ============================================================================
DELETE epp
FROM enrollment_payment_plans epp
JOIN _plans_to_keep k ON epp.enrollment_id = k.enrollment_id
WHERE epp.id != k.keeper_plan_id;

-- ============================================================================
-- Step 5: Verify no duplicates remain
-- ============================================================================
SELECT enrollment_id, COUNT(*) as cnt
FROM enrollment_payment_plans
GROUP BY enrollment_id
HAVING COUNT(*) > 1;

-- If the above returns 0 rows, proceed:

-- ============================================================================
-- Step 6: Add the UNIQUE constraint
-- ============================================================================
ALTER TABLE `enrollment_payment_plans`
    ADD UNIQUE KEY `uk_epp_enrollment` (`enrollment_id`);

-- ============================================================================
-- Cleanup
-- ============================================================================
DROP TEMPORARY TABLE IF EXISTS _plans_to_keep;
SET FOREIGN_KEY_CHECKS = 1;
