<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use QueryPath\DOMQuery;

class PageStatistics {
    // primary types
    const LABEL = 'label';
    const VALUE = 'value';
    const FOUND = 'found';
    const TRANSFORM = 'transformed';
    const WARNING = 'warnings';
    const ERROR = 'errors';
    // message labels
    const ACTIONS = 'transformed: actions';
    // messages array keys
    const MESSAGE = 'message';
    const COUNT = 'count';
    // options
    const TRANSFORM_ALL = 'transformAll';
    const WARN_IF_FOUND = 'warnIfFound';
    const WARN_IF_MISSING = 'warnIfMissing';
    const ERROR_IF_FOUND = 'errorIfFound';
    const ERROR_IF_MISSING = 'errorIfMissing';

    protected $stats = array();

    protected $elementCount;

    public static function getMessagesLabelFor($type) {
        $map = array(
            self::TRANSFORM => self::ACTIONS);
        if (array_key_exists($type, $map))
            return $map[$type];
        return "{$type}: messages";
    }

    /**
     * @param string $label
     * @param string|bool|float|int $value
     * @param array $options An associative array of options
     * Possible options:
     * * warnings: an int representing the number of warnings
     * * warningMessages: warning message(s) (see note)
     * * errors: an int representing the number of errors
     * * errorMessages: error message(s) (see note)
     *
     * Note: Message options can be null, a string, an array of strings, or an
     * array of assoc. arrays with the inner arrays having keys: message, count
     */
    public function addValueStat($label, $value, array $options = array()) {
        $types = array(self::WARNING, self::ERROR);
        $stat = $this->createStat(self::VALUE, $value);
        $stat = $this->buildStat($stat, $types, $options);
        $this->stats[$label] = $stat;
    }

    /**
     * @param string $label
     * @param int $found The number of matches.
     * @param array $options An associative array of options.
     * Possible options:
     * * transformAll: a bool indicating whether all matches should be marked as transformed
     * * transformed: an int representing the number of transformations performed
     * * transformMessages: description(s) of transformations performed (see note)
     * * warnIfFound: a bool indicating whether to warn if any matches were found
     * * warnIfMissing: a bool indicating whether to warn if no matches were found
     * * warnings: an int representing the number of warnings
     * * warningMessages: warning message(s) (see note)
     * * errorIfFound: a bool indicating whether to error if any matches were found
     * * errorIfMissing: a bool indicating whether to error if no matches were found
     * * errors: an int representing the number of errors
     * * errorMessages: error message(s) (see note)
     *
     * Note: Message options can be null, a string, an array of strings, or an
     * array of assoc. arrays with the inner arrays having keys: message, count.
     * Also, there should be at most one of transformAll and transformed,
     * at most one of warnIfFound, warnIfMissing, and warnings, and
     * at most one of errorIfFound, errorIfMissing, and errors.
     */
    public function addTransformStat($label, $found, array $options = array()) {
        $types = array(self::TRANSFORM, self::WARNING, self::ERROR);
        $options = $this->normalizeStatOptions($found, $options);

        $stat = $this->createStat(self::FOUND, $found);
        $stat = $this->buildStat($stat, $types, $options);
        $this->stats[$label] = $stat;
    }

    /**
     * Add transform stat based on current matches in a QueryPath DOMQuery object.
     * @param string $label
     * @param \QueryPath\DOMQuery $query The query with matches to get stats on.
     * @param array $options See addTransformStat() options, especially
     * transformAll, warnIfFound, warnIfMissing, errorIfFound, errorIfMissing.
     */
    public function addQueryStat($label, DOMQuery $query,
                                 array $options = array()) {
        $this->addTransformStat($label, $query->count(), $options);
    }

    /**
     * @param string $label
     * @param string $type
     * @param int $count
     * @param mixed|null $messages
     */
    public function incrementStat($label, $type, $count = 1, $messages = null) {
        if (!array_key_exists($label, $this->stats)) {
            $this->stats[$label] = $this->createStat($type, $count, $messages);
            return;
        }
        $stat = $this->stats[$label];
        if (array_key_exists($type, $stat)) {
            $stat[$type] += $count;
        } else {
            $stat[$type] = $count;
        }
        $this->appendMessages($stat, $type, $messages);
        $this->stats[$label] = $stat;
    }

    public function beginTransform(DOMQuery $qp) {
        $qp = $qp->top('body');
        $this->elementCount = RtfmQueryPath::countAll($qp);
    }

    public function checkTransform($statLabel, DOMQuery $qp, $matchesCount,
                                   $expectedElementChangesPerMatch) {
        $qp = $qp->top('body');
        $beginCount = $this->elementCount;
        $endCount = RtfmQueryPath::countAll($qp);
        $actual = $endCount - $beginCount;
        $expected = $matchesCount * $expectedElementChangesPerMatch;
        if ($actual !== $expected)
            $this->incrementStat($statLabel, self::WARNING, 1,
                "Changed element count does not match expected. Expected: {$expected} Actual: {$actual}");
    }

    /**
     * @deprecated
     */
    public function add($label, $value, $isTransformed = null, $isWarning = null) {
        $stat = array('label' => $label, 'value' => $value);
        if (!is_null($isTransformed))
            $stat['transformed'] = $isTransformed;
        if (!is_null($isWarning))
            $stat['warning'] = $isWarning;
        $this->stats[$label] = $stat;
    }

    /**
     * @deprecated
     */
    public function addCountStat($label, $count, $isTransformed = null,
                                 $warnIfFound = null, $isRequired = null) {
        $isWarning = $count > 0 ? $warnIfFound : $isRequired;
        if ($count === 0)
            $isTransformed = false;
        $this->add($label, $count, $isTransformed, $isWarning);
    }

    public function getStats() {
        return $this->stats;
    }

    protected function createStat($type = null, $value = null, $messages = null) {
        $stat = array();
        $stat = $this->addToStat($stat, $type, $value, $messages);
        return $stat;
    }

    protected function buildStat(array $stat, array $types, array $options) {
        foreach ($types as $type)
            $stat = $this->addToStatFromOptions($stat, $type, $options);
        return $stat;
    }

    protected function addToStatFromOptions(array $stat, $type, array $options) {
        $optionsKeys = array(
            self::TRANSFORM => array('valueKey' => self::TRANSFORM, 'messagesKey' => 'transformMessages'),
            self::WARNING => array('valueKey' => self::WARNING, 'messagesKey' => 'warningMessages'),
            self::ERROR => array('valueKey' => self::ERROR, 'messagesKey' => 'errorMessages')
        );
        $keys = $optionsKeys[$type];
        $stat = $this->addToStat($stat, $type,
            $this->getOption($options, $keys['valueKey']),
            $this->getOption($options, $keys['messagesKey']));
        return $stat;
    }

    protected function getOption(array $options, $key) {
        return array_key_exists($key, $options) ? $options[$key] : null;
    }

    // TODO: smarter message handling (empty string, consolidate duplicates from input)
    /**
     * Add a value and message(s) to the stat for the given type.
     * This only creates a new entry in the stat, it won't update one.
     * @param array $stat
     * @param string $type Type can be one of self::FOUND, self::TRANSFORM,
     * self::WARNING, self::ERROR
     * @param bool|float|int|string|null $value
     * @param null $messages
     * @return array The stat array
     */
    protected function addToStat(array $stat, $type, $value, $messages = null) {
        if (is_null($value))
            return $stat;
        $stat[$type] = $value;
        if (!is_null($messages))
            $stat[$this->getMessagesLabelFor($type)] = $messages;
        return $stat;
    }

    // TODO: smarter message handling (empty string, consolidate duplicates from input)
    protected function appendMessages(array &$stat, $type, $messages) {
        $msgKey = $this->getMessagesLabelFor($type);
        if (!array_key_exists($msgKey, $stat)) {
            $stat[$msgKey] = $messages;
            return;
        }

        if (is_string($stat[$msgKey]))
            $stat[$msgKey] = array($stat[$msgKey]);
        if (is_string($messages))
            $messages = array($messages);

        foreach ($messages as $msg)
            $this->appendMessage($stat[$msgKey], $msg);
    }

    protected function appendMessage(array &$messages, $newMessage) {
        $index = $this->findMessage($newMessage, $messages);
        if ($index === false) {
            $messages[] = $newMessage;
            return;
        }
        $messages[$index] = $this->mergeTwoMessages($messages[$index], $newMessage);
    }

    /**
     * @param string|array $needle
     * @param array $haystack
     * @return int|bool the index or false
     */
    protected function findMessage($needle, array $haystack) {
        foreach ($haystack as $index => $message) {
            if ($this->isSameMessage($needle, $message))
                return $index;
        }
        return false;
    }

    protected function isSameMessage($message1, $message2) {
        return $this->getMessageText($message1) === $this->getMessageText($message2);
    }

    // assumes messages are the same
    protected function mergeTwoMessages($message1, $message2) {
        $count1 = is_array($message1) ? $message1[self::COUNT] : 1;
        $count2 = is_array($message2) ? $message2[self::COUNT] : 1;
        return array(self::MESSAGE => $this->getMessageText($message1),
            self::COUNT => $count1 + $count2);
    }

    protected function getMessageText($message) {
        if (is_array($message))
            return $message[self::MESSAGE];
        return $message;
    }

    /**
     * Applies transformAll, warnIfFound, warnIfMissing, errorIfFound and
     * errorIfMissing options.
     *
     * @param int $found
     * @param array $options see addTransformStat() options
     * @return array The normalized options.
     *
     * Note: this doesn't check if transformed is set before overwriting with
     * transformAll nor warnings before overwriting with warnIfFound or
     * warnIfMissing, etc.
     */
    protected function normalizeStatOptions($found, array $options = array()) {
        if ($this->getOption($options, self::TRANSFORM_ALL) && $found > 0) {
            $options[self::TRANSFORM] = $found;
            unset($options[self::TRANSFORM_ALL]);
        }
        if ($this->getOption($options, self::WARN_IF_FOUND) && $found > 0) {
            $options[self::WARNING] = $found;
            unset($options[self::WARN_IF_FOUND]);
        }
        if ($this->getOption($options, self::WARN_IF_MISSING) && $found == 0) {
            $options[self::WARNING] = 1;
            unset($options[self::WARN_IF_MISSING]);
        }
        if ($this->getOption($options, self::ERROR_IF_FOUND) && $found > 0) {
            $options[self::ERROR] = $found;
            unset($options[self::ERROR_IF_FOUND]);
        }
        if ($this->getOption($options, self::ERROR_IF_MISSING) && $found == 0) {
            $options[self::ERROR] = 1;
            unset($options[self::ERROR_IF_MISSING]);
        }

        return $options;
    }
}
