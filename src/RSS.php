<?php
namespace Pharaonic\RSS;

/**
 * RSS Generator  1.0.0
 * 
 * @method setTitle()
 * @method setDescription()
 * @method setLink()
 * @method setImage()
 * @method setLanguage()
 * @method setCopyright()
 * @method setPublished()
 * @method setUpdated()
 * @method render()
 * 
 * @author Moamen Eltouny (Raggi) <raggi@raggitech.com>
 */
class RSS
{
    /**
     * Channel fields and items
     *
     * @var array
     */
    private $channel = [
        'title'         => null,
        'description'   => null,
        'link'          => null,
        'image'         => [
            'url'           => null,
            'width'         => 88,
            'height'        => 31
        ],

        'language'      => null,
        'copyright'     => null,
        'published'     => null,
        'updated'       => null,

        'items'         => []
    ];

    /**
     * Channel title.
     *
     * @param string $title
     * @return static
     */
    public function setTitle(string $title)
    {
        $this->channel['title'] = $title;

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
        $this->channel['description'] = $description;

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
        $this->channel['link'] = $link;

        return $this;
    }

    /**
     * Channel image that can be displayed with the channel.
     *
     * @param string $url
     * @param integer $width
     * @param integer $height
     * @return static
     */
    public function setImage(string $url, int $width = 88, int $height = 31)
    {
        $this->channel['image']['url'] = $url;
        $this->channel['image']['width'] = $width;
        $this->channel['image']['height'] = $height;

        return $this;
    }

    /**
     * Channel : Content language
     *
     * @param string $language
     * @return static
     */
    public function setLanguage(string $language)
    {
        $this->channel['language'] = $language;

        return $this;
    }

    /**
     * Channel : Content copyright
     *
     * @param string $copyright
     * @return static
     */
    public function setCopyright(string $copyright)
    {
        $this->channel['copyright'] = $copyright;

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
        $this->channel['published'] = $published;

        return $this;
    }

    /**
     * Channel : Last build date
     *
     * @param string $updated
     * @return static
     */
    public function setUpdated(string $updated)
    {
        $this->channel['updated'] = $updated;

        return $this;
    }

    /**
     * Channel : add item
     *
     * @param RSSItem $item
     * @return static
     */
    public function setItem(RSSItem $item)
    {
        $this->channel['items'][] = $item;

        return $this;
    }

    /**
     * Render the RSS content (XML)
     *
     * @param boolean $withContentType
     * @return void
     */
    public function render($withContentType = true)
    {
        if (empty($this->channel['title']) || empty($this->channel['description']) || empty($this->channel['link']))
            throw new \Exception('You have to set Title, Description and Link of the Channel.');

        if ($withContentType) @header('Content-Type: text/xml; charset=utf-8');

        // PREPARE
        $doc = xmlwriter_open_memory();
        xmlwriter_set_indent($doc, true);
        xmlwriter_start_document($doc, '1.0', 'UTF-8');
        xmlwriter_start_element($doc, 'rss');
        xmlwriter_write_attribute($doc, 'version', '2.0');
        xmlwriter_start_element($doc, 'channel');
        xmlwriter_write_element($doc, 'generator', 'Pharaonic RSS Generator');


        // Title, Description and Link
        xmlwriter_write_element($doc, 'title', $this->channel['title']);
        xmlwriter_write_element($doc, 'description', $this->channel['description']);
        xmlwriter_write_element($doc, 'link', $this->channel['link']);

        // Image
        if (!empty($this->channel['image']['url'])) {
            xmlwriter_start_element($doc, 'image');
            xmlwriter_write_element($doc, 'title', $this->channel['title']);
            xmlwriter_write_element($doc, 'link', $this->channel['link']);
            xmlwriter_write_element($doc, 'url', $this->channel['image']['url']);
            xmlwriter_write_element($doc, 'width', $this->channel['image']['width']);
            xmlwriter_write_element($doc, 'height', $this->channel['image']['height']);
            xmlwriter_end_element($doc);
        }

        // Language & Copyright & Published & Updated
        if (!empty($this->channel['language'])) xmlwriter_write_element($doc, 'language', $this->channel['language']);
        if (!empty($this->channel['copyright'])) xmlwriter_write_element($doc, 'copyright', $this->channel['copyright']);
        xmlwriter_write_element($doc, 'pubDate', $this->channel['published'] ?? date('r', time()));
        if (!empty($this->channel['updated'])) xmlwriter_write_element($doc, 'lastBuildDate', $this->channel['updated']);

        // Items
        foreach ($this->channel['items'] as $item)
            $item->render($doc);

        xmlwriter_end_element($doc);
        xmlwriter_end_element($doc);
        // OUTPUT
        return xmlwriter_output_memory($doc);
    }

    /**
     * Getting RSS content (XML)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(false);
    }
}
