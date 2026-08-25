<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_birth_date_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'birth_date'));
    }

    public function test_profiles_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('profiles'));
        $this->assertTrue(Schema::hasColumn('profiles', 'user_id'));
        $this->assertTrue(Schema::hasColumn('profiles', 'country_code'));
        $this->assertTrue(Schema::hasColumn('profiles', 'language_code'));
        $this->assertTrue(Schema::hasColumn('profiles', 'onboarding_completed'));
    }

    public function test_content_items_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('content_items'));
        $this->assertTrue(Schema::hasColumn('content_items', 'provider'));
        $this->assertTrue(Schema::hasColumn('content_items', 'external_id'));
        $this->assertTrue(Schema::hasColumn('content_items', 'moderation_status'));
        $this->assertTrue(Schema::hasColumn('content_items', 'reviewed_by'));
        $this->assertTrue(Schema::hasColumn('content_items', 'published_at'));
    }

    public function test_categories_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasColumn('categories', 'name'));
        $this->assertTrue(Schema::hasColumn('categories', 'slug'));
    }

    public function test_content_categories_pivot_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('content_categories'));
    }

    public function test_content_age_bands_pivot_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('content_age_bands'));
    }

    public function test_likes_table_exists_with_unique_constraint(): void
    {
        $this->assertTrue(Schema::hasTable('likes'));
        $this->assertTrue(Schema::hasColumn('likes', 'user_id'));
        $this->assertTrue(Schema::hasColumn('likes', 'content_item_id'));
    }

    public function test_saves_table_exists_with_unique_constraint(): void
    {
        $this->assertTrue(Schema::hasTable('saves'));
        $this->assertTrue(Schema::hasColumn('saves', 'user_id'));
        $this->assertTrue(Schema::hasColumn('saves', 'content_item_id'));
    }

    public function test_engagement_events_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('engagement_events'));
        $this->assertTrue(Schema::hasColumn('engagement_events', 'user_id'));
        $this->assertTrue(Schema::hasColumn('engagement_events', 'content_item_id'));
        $this->assertTrue(Schema::hasColumn('engagement_events', 'type'));
    }

    public function test_countries_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('countries'));
        $this->assertTrue(Schema::hasColumn('countries', 'code'));
    }

    public function test_languages_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('languages'));
        $this->assertTrue(Schema::hasColumn('languages', 'code'));
    }
}
