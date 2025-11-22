<?php

namespace Syofyanzuhad\FilamentChatflow\Services;

use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowStep;

class ChatflowBuilder
{
    public function validateFlow(Chatflow $chatflow): array
    {
        $errors = [];

        // Check if chatflow has at least one step
        if ($chatflow->steps()->count() === 0) {
            $errors[] = 'Chatflow must have at least one step.';
        }

        // Check if chatflow has a root step (starting point)
        if ($chatflow->rootSteps()->count() === 0) {
            $errors[] = 'Chatflow must have a root step (starting point).';
        }

        // Check for orphaned steps (steps with no path to them)
        $orphanedSteps = $this->findOrphanedSteps($chatflow);
        if (! empty($orphanedSteps)) {
            $errors[] = 'Found ' . count($orphanedSteps) . ' orphaned steps with no path leading to them.';
        }

        // Check for question steps without options
        $questionStepsWithoutOptions = $chatflow->steps()
            ->where('type', ChatflowStep::TYPE_QUESTION)
            ->whereNull('options')
            ->count();

        if ($questionStepsWithoutOptions > 0) {
            $errors[] = 'Found ' . $questionStepsWithoutOptions . ' question steps without options.';
        }

        // Check for circular references
        $circularReferences = $this->findCircularReferences($chatflow);
        if (! empty($circularReferences)) {
            $errors[] = 'Found circular references in the flow.';
        }

        // Check if flow has at least one end step
        if ($chatflow->steps()->where('type', ChatflowStep::TYPE_END)->count() === 0) {
            $errors[] = 'Chatflow must have at least one end step.';
        }

        return $errors;
    }

    public function duplicateFlow(Chatflow $chatflow, string $newName): Chatflow
    {
        $newChatflow = $chatflow->replicate();
        $newChatflow->name = $newName;
        $newChatflow->is_active = false;
        $newChatflow->save();

        $stepMapping = [];

        // First pass: Create all steps
        foreach ($chatflow->steps as $step) {
            $newStep = $step->replicate();
            $newStep->chatflow_id = $newChatflow->id;
            $newStep->parent_id = null;
            $newStep->next_step_id = null;
            $newStep->save();

            $stepMapping[$step->id] = $newStep->id;
        }

        // Second pass: Update relationships
        foreach ($chatflow->steps as $step) {
            $newStep = ChatflowStep::find($stepMapping[$step->id]);

            if ($step->parent_id && isset($stepMapping[$step->parent_id])) {
                $newStep->parent_id = $stepMapping[$step->parent_id];
            }

            if ($step->next_step_id && isset($stepMapping[$step->next_step_id])) {
                $newStep->next_step_id = $stepMapping[$step->next_step_id];
            }

            // Update options with new step IDs
            if ($step->options) {
                $newOptions = collect($step->options)->map(function ($option) use ($stepMapping) {
                    if (isset($option['next_step_id']) && isset($stepMapping[$option['next_step_id']])) {
                        $option['next_step_id'] = $stepMapping[$option['next_step_id']];
                    }

                    return $option;
                })->toArray();

                $newStep->options = $newOptions;
            }

            $newStep->save();
        }

        return $newChatflow;
    }

    protected function findOrphanedSteps(Chatflow $chatflow): array
    {
        $allSteps = $chatflow->steps()->pluck('id')->toArray();
        $rootSteps = $chatflow->rootSteps()->pluck('id')->toArray();
        $reachableSteps = [];

        // Build a set of reachable steps
        $queue = $rootSteps;
        while (! empty($queue)) {
            $currentStepId = array_shift($queue);
            if (in_array($currentStepId, $reachableSteps)) {
                continue;
            }
            $reachableSteps[] = $currentStepId;

            $step = ChatflowStep::find($currentStepId);
            if (! $step) {
                continue;
            }

            // Add next_step_id to queue
            if ($step->next_step_id && ! in_array($step->next_step_id, $reachableSteps)) {
                $queue[] = $step->next_step_id;
            }

            // Add child steps to queue
            foreach ($step->children as $child) {
                if (! in_array($child->id, $reachableSteps)) {
                    $queue[] = $child->id;
                }
            }

            // Add option next_step_ids to queue
            if ($step->options) {
                foreach ($step->options as $option) {
                    if (isset($option['next_step_id']) && ! in_array($option['next_step_id'], $reachableSteps)) {
                        $queue[] = $option['next_step_id'];
                    }
                }
            }
        }

        return array_diff($allSteps, $reachableSteps);
    }

    protected function findCircularReferences(Chatflow $chatflow): array
    {
        $circular = [];
        $visited = [];
        $recursionStack = [];

        foreach ($chatflow->rootSteps as $rootStep) {
            if ($this->detectCircular($rootStep, $visited, $recursionStack)) {
                $circular[] = $rootStep->id;
            }
        }

        return $circular;
    }

    protected function detectCircular(ChatflowStep $step, array &$visited, array &$recursionStack): bool
    {
        $visited[$step->id] = true;
        $recursionStack[$step->id] = true;

        // Check next_step_id
        if ($step->next_step_id) {
            if (! isset($visited[$step->next_step_id])) {
                $nextStep = ChatflowStep::find($step->next_step_id);
                if ($nextStep && $this->detectCircular($nextStep, $visited, $recursionStack)) {
                    return true;
                }
            } elseif (isset($recursionStack[$step->next_step_id])) {
                return true;
            }
        }

        // Check option next_step_ids
        if ($step->options) {
            foreach ($step->options as $option) {
                if (isset($option['next_step_id'])) {
                    $nextStepId = $option['next_step_id'];
                    if (! isset($visited[$nextStepId])) {
                        $nextStep = ChatflowStep::find($nextStepId);
                        if ($nextStep && $this->detectCircular($nextStep, $visited, $recursionStack)) {
                            return true;
                        }
                    } elseif (isset($recursionStack[$nextStepId])) {
                        return true;
                    }
                }
            }
        }

        unset($recursionStack[$step->id]);

        return false;
    }

    public function exportFlow(Chatflow $chatflow): array
    {
        return [
            'chatflow' => $chatflow->toArray(),
            'steps' => $chatflow->steps->toArray(),
        ];
    }

    public function importFlow(array $data): Chatflow
    {
        $chatflowData = $data['chatflow'];
        unset($chatflowData['id'], $chatflowData['created_at'], $chatflowData['updated_at']);

        $chatflow = Chatflow::create($chatflowData);

        $stepMapping = [];

        // First pass: Create steps
        foreach ($data['steps'] as $stepData) {
            $oldId = $stepData['id'];
            unset($stepData['id'], $stepData['created_at'], $stepData['updated_at']);

            $stepData['chatflow_id'] = $chatflow->id;
            $stepData['parent_id'] = null;
            $stepData['next_step_id'] = null;

            $step = ChatflowStep::create($stepData);
            $stepMapping[$oldId] = $step->id;
        }

        // Second pass: Update relationships
        foreach ($data['steps'] as $stepData) {
            $newStep = ChatflowStep::find($stepMapping[$stepData['id']]);

            if ($stepData['parent_id'] && isset($stepMapping[$stepData['parent_id']])) {
                $newStep->parent_id = $stepMapping[$stepData['parent_id']];
            }

            if ($stepData['next_step_id'] && isset($stepMapping[$stepData['next_step_id']])) {
                $newStep->next_step_id = $stepMapping[$stepData['next_step_id']];
            }

            if ($stepData['options']) {
                $newOptions = collect($stepData['options'])->map(function ($option) use ($stepMapping) {
                    if (isset($option['next_step_id']) && isset($stepMapping[$option['next_step_id']])) {
                        $option['next_step_id'] = $stepMapping[$option['next_step_id']];
                    }

                    return $option;
                })->toArray();

                $newStep->options = $newOptions;
            }

            $newStep->save();
        }

        return $chatflow;
    }
}
