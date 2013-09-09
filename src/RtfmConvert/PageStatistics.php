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

    private $stats = array();

    public static function getMessagesLabelFor($type) {
        $map = array(
            self::TRANSFORM => self::ACTIONS);
        if (array_key_exists($type, $map))
            return $map[$type];
        return "{$type}: messages";
    }

    /**
     * Message arguments can be null, a string, an array of strings, or an
     * array of assoc. arrays with the inner arrays having keys: message, count
     *
     * @param string $label
     * @param string|bool|float|int $value
     * @param null $warning
     * @param null $warningMessage
     */
    public function addValueStat($label, $value, $warning = null,
        $warningMessage = null) {
        $stat = $this->createStat(self::VALUE, $value);
        $stat = $this->addToStat($stat, self::WARNING, $warning,
            $warningMessage);
        $this->stats[$label] = $stat;
    }

    /**
     * Message arguments can be null, a string, an array of strings, or an
     * array of assoc. arrays with the inner arrays having keys: message, count
     *
     * @param $label
     * @param $found
     * @param int|null $transformed
     * @param string|array|null $transformedMessages
     * @param int|null $warnings
     * @param string|array|null $warningMessages
     * @param int|null $errors
     * @param string|array|null $errorMessages
     */
    public function addTransformStat($label, $found, $transformed = null,
                                     $transformedMessages = null,
                                     $warnings = null, $warningMessages = null,
                                     $errors = null, $errorMessages = null) {
        $stat = $this->createStat(self::FOUND, $found);
        $stat = $this->addToStat($stat, self::TRANSFORM, $transformed,
            $transformedMessages);
        $stat = $this->addToStat($stat, self::WARNING, $warnings,
            $warningMessages);
        $stat = $this->addToStat($stat, self::ERROR, $errors, $errorMessages);
        $this->stats[$label] = $stat;
    }

    /**
     * Message arguments can be null, a string, an array of strings, or an
     * array of assoc. arrays with the inner arrays having keys: message, count
     *
     * @param \QueryPath\DOMQuery $query
     * @param string $label
     * @param bool $transformAll
     * @param string $transformMessage
     * @param bool $warnIfFound
     * @param bool $warnIfMissing
     * @param string $warningMessage
     */
    public function addQueryStat(DOMQuery $query, $label,
                                 $transformAll = false, $transformMessage = null,
                                 $warnIfFound = false, $warnIfMissing = false,
                                 $warningMessage = null) {
        $found = $query->count();
        $transformed = $transformAll ? $found : null;
        $warnings = null;
        if ($warnIfFound && $found > 0)
            $warnings = $found;
        if ($warnIfMissing && $found == 0)
            $warnings = true;
        $this->addTransformStat($label, $found, $transformed, $transformMessage,
            $warnings, $warningMessage);
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
}
