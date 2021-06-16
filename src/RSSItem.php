<?php
namespace Pharaonic\RSS;

/**
 * RSS Item Generator  1.0.0
 * 
 * @method setGUID()
 * @method setTitle()
 * @method setDescription()
 * @method setLink()
 * @method setAuthor()
 * @method setCategory()
 * @method setPublished()
 * @method appendToChannel()
 * @method render()
 * 
 * @author Moamen Eltouny (Raggi) <raggi@raggitech.com>
 */
class RSSItem
{
    /**
     * item fields
     *
     * @var array
     */
    private $item = [
        'guid'         => null,
        'title'         => null,
        'description'   => null,
        'link'          => null,

        'published'     => null,
        'author'        => null,
        'categories'    => []
    ];

    /**
     * Channel GUID.
     *
     * @param string $guid
     * @return static
     */
    public function setGUID(string $guid)
    {
        $this->item['guid'] = $guid;

        return $this;
    }

    /**
     * Channel title.
     *
     * @param string $title
     * @return static
     */
    public function setTitle(string $title)
    {
        $this->item['title'] = $title;

        return $this;
    }

    /**
     * Channel description
     *
     * @param string $description
     * @return static
     */
    public function setDescription(string $description)
    {
        $this->item['description'] = $description;

        return $this;
    }

    /**
     * Channel URL
     *
     * @param string $link
     * @return static
     */
    public function setLink(string $link)
    {
        $this->item['link'] = $link;

        return $this;
    }

    /**
     * Channel : Content author
     *
     * @param string $author
     * @return static
     */
    public function setAuthor(string $author)
    {
        $this->item['author'] = $author;

        return $this;
    }

    /**
     * Channel : Content category
     *
     * @param string $category
     * @return static
     */
    public function setCategory(string $category)
    {
        $this->item['categories'][] = $category;

        return $this;
    }

    /**
     * Channel : Publication date
     *
     * @param string $published
     * @return static
     */
    public function setPublished(string $published)
    {
        $this->item['published'] = $published;

        return $this;
    }

    /**
     * Append Item to Channel
     *
     * @param RSS $channel
     * @return static
     */
    public function appendToChannel(RSS $channel)
    {
        $channel->setItem($this);

        return $this;
    }

    /**
     * Render the item and add it to the Doc
     *
     * @param resource $doc
     * @return void
     */
    public function render(&$doc)
    {
        if (empty($this->item['title']) || empty($this->item['description']) || empty($this->item['link']))
            throw new \Exception('You have to set Title, Description and Link of the Item.');

        xmlwriter_start_element($doc, 'item');

        // Title, Description and Link
        xmlwriter_write_element($doc, 'title', $this->item['title']);
        xmlwriter_write_element($doc, 'description', $this->item['description']);
        xmlwriter_write_element($doc, 'link', $this->item['link']);

        // GUID &Categories & Author & Published
        foreach ($this->item['categories'] as $category) xmlwriter_write_element($doc, 'category', $category);
        if (!empty($this->item['author'])) xmlwriter_write_element($doc, 'author', $this->item['author']);
        if (!empty($this->item['published'])) xmlwriter_write_element($doc, 'pubDate', $this->item['published']);
        if (!empty($this->item['guid'])) xmlwriter_write_element($doc, 'guid', $this->item['guid']);


        xmlwriter_end_element($doc);
    }
}
