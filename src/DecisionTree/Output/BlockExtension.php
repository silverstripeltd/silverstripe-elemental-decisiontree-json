<?php

namespace Silverstripe\Elemental\DecisionTree\Output;

use DNADesign\SilverStripeElementalDecisionTree\Model\DecisionTreeStep;
use JsonException;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\Parsers\ShortcodeParser;

/**
 * The main block extension that provide the method to return the JSON string for used with frontend implementation.
 */
class BlockExtension extends Extension
{
    protected const STRING CACHE_KEY = 'output';

    /**
     * Holds collection of tree data while JSON is created
     */
    protected array $treeData = [];

    /**
     * After publish refresh the cache
     */
    public function onAfterPublish(): void
    {
        $this->getCacheInstance()->clear();
        $this->getCachedJson();
    }

    /**
     * On delete clear the cache
     */
    public function onBeforeDelete(): void
    {
        $this->getCacheInstance()->clear();
    }

    /**
     * Get all tree data in JSON string for front ends implementation.
     */
    public function getJson(bool $useCache = false): string
    {
        return $useCache
            ? $this->getCachedJson()
            : $this->getFreshJson();
    }

    /**
     * Get tree data from cache
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function getCachedJson(): string
    {
        $cache = $this->getCacheInstance();

        if (!$cache->has(self::CACHE_KEY)) {
            $data = $this->getFreshJson();
            $cache->set(self::CACHE_KEY, $data);
        }

        return $cache->get(self::CACHE_KEY);
    }

    /**
     * Get tree data from source
     *
     * @throws \Exception
     */
    protected function getFreshJson(): string
    {
        try {
            return base64_encode(json_encode(array_merge(
                [
                    'blockIntro' => ShortcodeParser::get()->parse($this->getOwner()->Introduction),
                    'blockTitle' => $this->getOwner()->Title,
                ],
                $this->getTreeData(),
            ), JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            return '{}';
        }
    }

    /**
     * Get instance of the cache
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getCacheInstance(): CacheInterface
    {
        return Injector::inst()->get(CacheInterface::class . '.decisiontree-output');
    }

    /**
     * Helper to extract the step and answer data from the block.
     *
     * @return array
     */
    private function getTreeData(): array
    {
        $this->treeData = [
            'answers' => [],
            'steps' => [],
        ];

        // start from the top
        $first = $this->getOwner()->FirstStep();

        if (!$first->exists()) {
            return $this->treeData;
        }

        // now start our descent through the flow
        $this->collectStepData($first, $first);

        // provide way to inject extra data
        $this->getOwner()->extend('updateTreeData', $this->treeData);

        return $this->treeData;
    }

    /**
     * Helper to collect the data for a given step.
     */
    private function collectStepData(DecisionTreeStep $step, DecisionTreeStep $first): void
    {
        // add step data into array
        $this->treeData['steps'][$step->ID] = $step->getJsonData();

        // collect answer data into the array
        $this->collectAnswersData($step->Answers(), $first);
    }

    /**
     * Helper to collect the data for the given answers.
     */
    private function collectAnswersData(SS_List $answers, DecisionTreeStep $first): void
    {
        // loop through answers
        foreach ($answers as $answer) {
            // push answer data into array
            $this->treeData['answers'][$answer->ID] = $answer->getJsonData();

            // check for next step
            $step = $answer->ResultingStep();

            // recursively collect the next step data
            if (!($step instanceof DecisionTreeStep) || $step->ID === $first->ID || !$step->exists()) {
                continue;
            }

            $this->collectStepData($step, $first);
        }
    }
}
