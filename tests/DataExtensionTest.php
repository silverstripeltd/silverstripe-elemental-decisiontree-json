<?php

namespace Silverstripe\Elemental\Tests;

use DNADesign\SilverStripeElementalDecisionTree\Model\DecisionTreeStep;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\List\SS_List;

/**
 * @phpcs:disable SlevomatCodingStandard.Files.FunctionLength.FunctionLength
 * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
 * @phpcs:disable SlevomatCodingStandard.Classes.ForbiddenPublicProperty.ForbiddenPublicProperty
 */
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
            'Content' => 'Content',
            'HideTitle' => false,
            'Title' => 'Step',
            'Type' => 'Question',
        ]);
        $step->answers = ArrayList::create([
            ArrayData::create([
                'ID' => 1,
                'ResultingStepID' => 2,
                'Title' => 'Answer 1',
            ]),
            ArrayData::create([
                'ID' => 2,
                'ResultingStepID' => 3,
                'Title' => 'Answer 2',
            ]),
        ]);

        $this->assertEquals(
            [
                'answers' => [1, 2,],
                'content' => 'Content',
                'hideTitle' => false,
                'isFirst' => true,
                'isQuestion' => true,
                'title' => 'Step',
            ],
            $step->getJsonData(),
        );
    }

    public static function tearDownAfterClass(): void
    {
        // Disable as we don't have any db to reset
    }
}
