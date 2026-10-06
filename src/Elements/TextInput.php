<?php

namespace Pharaonic\Rss\Elements;

use Pharaonic\Rss\Exceptions\InvalidElementException;

/**
 * RSS channel <textInput> element: a text input box displayed with the channel.
 */
final class TextInput
{
    private string $title;

    private string $description;

    private string $name;

    private string $link;

    /**
     * @param string $title       Label of the submit button.
     * @param string $description Explanation of the text input area.
     * @param string $name        Name of the text object in the text input area.
     * @param string $link        URL of the script that processes text input requests.
     *
     * @throws InvalidElementException
     */
    public function __construct(string $title, string $description, string $name, string $link)
    {
        $fields = ['title' => $title, 'description' => $description, 'name' => $name, 'link' => $link];

        foreach ($fields as $field => $value) {
            if (trim($value) === '') {
                throw InvalidElementException::emptyValue('textInput', $field);
            }
        }

        $this->title = $title;
        $this->description = $description;
        $this->name = $name;
        $this->link = $link;
    }

    /**
     * @throws InvalidElementException
     */
    public static function make(string $title, string $description, string $name, string $link): self
    {
        return new self($title, $description, $name, $link);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
