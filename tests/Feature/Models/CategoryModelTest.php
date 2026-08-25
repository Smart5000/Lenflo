<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Content\ContentItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_content_items(): void
    {
        $category = Category::factory()->create();
        $content = ContentItem::factory()->create();
        $category->contentItems()->attach($content);

        $this->assertCount(1, $category->contentItems);
    }

    public function test_category_factory_creates_valid_category(): void
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->name);
        $this->assertNotNull($category->slug);
        $this->assertTrue($category->is_active);
    }
}
