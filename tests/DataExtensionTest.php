<?php

namespace SilverStripe\Elemental\Tests;

use DNADesign\SilverStripeElementalDecisionTree\Model\DecisionTreeStep;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\SS_List;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\View\ArrayData;

class DataExtensionTest extends SapphireTest
{
    public function testStepOutput(): void
    {
        $step = new class extends DecisionTreeStep {
            public ?SS_List $answers = null;

            public function Answers(): SS_List
            {
                if (is_null($this->answers)) {
                    $this->answers = ArrayList::create();
                }
                return $this->answers;
            }

            public function belongsToElement(): bool
            {
                return true;
            }
        };
        $step->update([
            'Title' => 'Step',
            'Type' => 'Question',
            'Content' => 'Content',
            'HideTitle' => false
        ]);
        $step->answers = ArrayList::create([
            ArrayData::create([
                'ID' => 1,
                'Title' => 'Answer 1',
                'ResultingStepID' => 2,
            ]),
            ArrayData::create([
                'ID' => 2,
                'Title' => 'Answer 2',
                'ResultingStepID' => 3,
            ]),
        ]);

        $this->assertEquals(
            [
                'title' => 'Step',
                'isQuestion' => true,
                'content' => 'Content',
                'hideTitle' => false,
                'answers' => [1, 2,],
                'isFirst' => true,
            ],
            $step->getJsonData()
        );
    }

    public static function tearDownAfterClass(): void
    {
        // Disable as we don't have any db to reset
    }
}
