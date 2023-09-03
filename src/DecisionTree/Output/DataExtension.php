<?php

namespace Silverstripe\Elemental\DecisionTree\Output;

use DNADesign\SilverStripeElementalDecisionTree\Model\DecisionTreeAnswer;
use DNADesign\SilverStripeElementalDecisionTree\Model\DecisionTreeStep;
use SilverStripe\Core\Extension;
use SilverStripe\View\Parsers\ShortcodeParser;

/**
 * Used within Step & Answer data object to provide a method to return collection of data to be used for JSON output
 */
class DataExtension extends Extension
{
    /**
     * Get collection of data for JSON output.
     */
    public function getJsonData(): array
    {
        $data = [];

        if ($this->getOwner() instanceof DecisionTreeStep) {
            $data = $this->getStepData();
        } elseif ($this->getOwner() instanceof DecisionTreeAnswer) {
            $data = $this->getAnswerData();
        }

        $this->getOwner()->extend('updateJsonData', $data);

        return $data;
    }

    /**
     * Get collection of step data for JSON output.
     *
     * @throws \Exception
     */
    protected function getStepData(): array
    {
        return [
            'answers' => $this->getOwner()->Answers()->column('ID'),
            'content' => ShortcodeParser::get()->parse($this->getOwner()->Content),
            'hideTitle' => (bool)$this->getOwner()->HideTitle,
            'isFirst' => $this->getOwner()->belongsToElement(),
            'isQuestion' => $this->getOwner()->Type === 'Question',
            'title' => $this->getOwner()->Title,
        ];
    }

    /**
     * Get collection of answer data for JSON output.
     */
    protected function getAnswerData(): array
    {
        return [
            'goTo' => $this->getOwner()->ResultingStepID,
            'title' => $this->getOwner()->Title,
        ];
    }
}
