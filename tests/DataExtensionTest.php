<?php

namespace SilverStripe\Elemental\Tests;

use DNADesign\SilverStripeElementalDecisionTree\Model\DecisionTreeAnswer;
use DNADesign\SilverStripeElementalDecisionTree\Model\DecisionTreeStep;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\SS_List;
use SilverStripe\Dev\SapphireTest;

class DataExtensionTest extends SapphireTest
{
    public function testStepOutput(): void
    {
        $step = new class extends DecisionTreeStep {
            public function getAnswers(): SS_List
            {
                return ArrayList::create();
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

        $this->assertEquals(
            [
                'title' => 'Step',
                'isQuestion' => true,
                'content' => 'Content',
                'hideTitle' => false,
                'answers' => [
                    [
                        'Title' => 'Answer 1',
                        'ResultingStepID' => 2,
                    ]
                ],
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
