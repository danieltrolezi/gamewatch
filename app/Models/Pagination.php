<?php

namespace App\Models;

use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class Pagination extends Model
{
    #[OA\Property(property: 'total', type: 'integer')]
    #[OA\Property(property: 'page_size', type: 'integer')]
    #[OA\Property(property: 'current_page', type: 'integer')]
    #[OA\Property(property: 'last_page', type: 'integer')]
    #[OA\Property(property: 'next_page_url', type: 'string')]
    #[OA\Property(property: 'prev_page_url', type: 'string')]
    #[OA\Property(property: 'data', type: 'array', items: new OA\Items(
        //ref: '#/components/schemas/Game'
        oneOf: [
            new OA\Schema(ref: '#/components/schemas/Game'),
        ]
    ))]

    public readonly int $lastPage;
    public readonly string $nextPageUrl;
    public readonly string $prevPageUrl;

    public function __construct(
        public readonly Collection $data,
        public readonly int $pageSize,
        public readonly int $currentPage,
        public readonly int $total
    ) {
        $this->lastPage = ceil($this->total / $this->pageSize);
        $this->nextPageUrl = $this->getPageUrl(1);
        $this->prevPageUrl = $this->getPageUrl(-1);
    }

    /**
     * @param integer $pageIncrement
     * @return string
     */
    private function getPageUrl(int $pageIncrement): string
    {
        if (
            $this->isFirstPageAndPrevLink($pageIncrement)
            || $this->isLastPageAndNextLink($pageIncrement)
        ) {
            return '';
        }

        $query = request()->query();
        $url = url()->current();
        $query['page'] = $this->currentPage + $pageIncrement;

        return $url . '?' . http_build_query($query);
    }

    /**
     * @param integer $pageIncrement
     * @return boolean
     */
    private function isFirstPageAndPrevLink(int $pageIncrement): bool
    {
        return $this->currentPage === 1 && $pageIncrement < 0;
    }

    /**
     * @param integer $pageIncrement
     * @return boolean
     */
    private function isLastPageAndNextLink(int $pageIncrement): bool
    {
        return $this->currentPage >= $this->lastPage && $pageIncrement > 0;
    }
}
