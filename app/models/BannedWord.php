<?php

class BannedWord extends Model
{
    private static ?array $cache = null;

    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM banned_words ORDER BY word');
    }

    public function add(string $word, string $action = 'block'): bool
    {
        $word = mb_strtolower(trim($word));
        if ($word === '') return false;
        try {
            $this->execute('INSERT INTO banned_words (word, action) VALUES (?, ?)', [$word, $action]);
            self::$cache = null;
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function remove(int $id): bool
    {
        self::$cache = null;
        return $this->execute('DELETE FROM banned_words WHERE id = ?', [$id]);
    }

    /**
     * Проверка текста по списку стоп-слов.
     * Возвращает ['block'] / ['flag'] / null + найденное слово.
     */
    public function check(string $text): ?array
    {
        if (self::$cache === null) {
            self::$cache = $this->fetchAll('SELECT word, action FROM banned_words');
        }
        $low = mb_strtolower($text);
        $hitFlag = null;
        foreach (self::$cache as $w) {
            if ($w['word'] !== '' && mb_strpos($low, $w['word']) !== false) {
                if ($w['action'] === 'block') {
                    return ['action' => 'block', 'word' => $w['word']]; // блок — приоритетнее
                }
                $hitFlag = ['action' => 'flag', 'word' => $w['word']];
            }
        }
        return $hitFlag;
    }
}
