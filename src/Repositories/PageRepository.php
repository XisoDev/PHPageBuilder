<?php

namespace PHPageBuilder\Repositories;

use PHPageBuilder\Contracts\PageContract;
use PHPageBuilder\Contracts\PageRepositoryContract;
use PHPageBuilder\Setting;
use Exception;

class PageRepository extends BaseRepository implements PageRepositoryContract
{
    /**
     * The pages database table.
     *
     * @var string
     */
    protected $table = 'pagebuilder_pages';

    /**
     * The class that represents each page.
     *
     * @var string
     */
    protected $class;

    /**
     * PageRepository constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->class = phpb_instance('page');
    }

    /**
     * Create a new page.
     *
     * @param array $data
     * @return bool|object|null
     * @throws Exception
     */
    public function create(array $data)
    {
        foreach (['name', 'layout'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field])) {
                return false;
            }
        }

        $page = parent::create([
            'name' => $data['name'],
            'layout' => $data['layout'],
        ]);
        if (! ($page instanceof PageContract)) {
            throw new Exception("Page not of type PageContract");
        }
        return $this->replaceTranslations($page, $data);
    }

    /**
     * Update the given page with the given updated data.
     *
     * @param $page
     * @param array $data
     * @return bool|object|null
     */
    public function update($page, array $data)
    {
        foreach (['name', 'layout'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field])) {
                return false;
            }
        }

        $this->replaceTranslations($page, $data);

        return parent::update($page, [
            'name' => $data['name'],
            'layout' => $data['layout'],
        ]);
    }

    /**
     * Replace the translations of the given page by the given data.
     *
     * @param PageContract $page
     * @param array $data
     * @return bool
     */
    protected function replaceTranslations(PageContract $page, array $data)
    {
        $activeLanguages = phpb_active_languages();
        foreach (['title', 'route'] as $field) {
            foreach ($activeLanguages as $locale) {
                if (! isset($data[$field][$locale])) {
                    return false;
                }
            }
        }

        $pageTranslationRepository = new PageTranslationRepository;
        $pageTranslationRepository->destroyWhere('page_id', $page->getId());
        foreach ($activeLanguages as $locale) {
            $pageTranslationRepository->create([
                'page_id' => $page->getId(),
                'locale' => $locale,
                'title' => $data['title'][$locale],
                'route' => $data['route'][$locale],
            ]);
        }

        return true;
    }

    /**
     * Update the given page with the given updated page data
     *
     * @param $page
     * @param array $data
     * @return bool|object|null
     */
    public function updatePageData($page, array $data)
    {
        return parent::update($page, [
            'data' => json_encode($data),
        ]);
    }
}
