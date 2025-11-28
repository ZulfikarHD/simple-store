<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test untuk Admin CategoryController
 * mencakup semua operasi CRUD kategori dengan validasi
 */
class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa guest tidak dapat akses halaman kategori
     */
    public function test_guest_cannot_access_categories_index(): void
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated user dapat melihat daftar kategori
     */
    public function test_authenticated_user_can_view_categories_list(): void
    {
        $user = User::factory()->admin()->create();
        Category::factory()->count(5)->create();

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Categories/Index')
            ->has('categories', 5));
    }

    /**
     * Test kategori menampilkan product count yang benar
     */
    public function test_categories_show_correct_product_count(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Categories/Index')
            ->where('categories.0.products_count', 5));
    }

    /**
     * Test dapat mengakses halaman create kategori
     */
    public function test_can_access_create_category_page(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.categories.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Categories/Create'));
    }

    /**
     * Test dapat membuat kategori baru tanpa gambar
     */
    public function test_can_create_category_without_image(): void
    {
        $user = User::factory()->admin()->create();

        $categoryData = [
            'name' => 'Kategori Baru',
            'description' => 'Deskripsi kategori baru',
            'is_active' => true,
            'sort_order' => 5,
        ];

        $response = $this->actingAs($user)->post(route('admin.categories.store'), $categoryData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', ['name' => 'Kategori Baru']);
    }

    /**
     * Test dapat membuat kategori baru dengan gambar
     */
    public function test_can_create_category_with_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->admin()->create();

        $categoryData = [
            'name' => 'Kategori Dengan Gambar',
            'description' => 'Deskripsi kategori',
            'is_active' => true,
            'sort_order' => 1,
            'image' => UploadedFile::fake()->image('category.jpg', 400, 400),
        ];

        $response = $this->actingAs($user)->post(route('admin.categories.store'), $categoryData);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Kategori Dengan Gambar']);

        $category = Category::where('name', 'Kategori Dengan Gambar')->first();
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    /**
     * Test validasi saat membuat kategori
     */
    public function test_create_category_validation(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('admin.categories.store'), []);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test validasi nama kategori harus unique
     */
    public function test_category_name_must_be_unique(): void
    {
        $user = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Kategori Existing']);

        $response = $this->actingAs($user)->post(route('admin.categories.store'), [
            'name' => 'Kategori Existing',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test dapat mengakses halaman edit kategori
     */
    public function test_can_access_edit_category_page(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.categories.edit', $category));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Categories/Edit')
            ->has('category'));
    }

    /**
     * Test dapat mengupdate kategori
     */
    public function test_can_update_category(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Kategori Diupdate',
            'description' => 'Deskripsi baru',
            'is_active' => true,
            'sort_order' => 10,
        ];

        $response = $this->actingAs($user)->put(route('admin.categories.update', $category), $updateData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Kategori Diupdate']);
    }

    /**
     * Test update kategori bisa pakai nama yang sama (sendiri)
     */
    public function test_can_update_category_with_same_name(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Nama Asli']);

        $updateData = [
            'name' => 'Nama Asli',
            'description' => 'Deskripsi diubah',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->put(route('admin.categories.update', $category), $updateData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
    }

    /**
     * Test dapat menghapus kategori yang tidak memiliki produk
     */
    public function test_can_delete_category_without_products(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Test tidak dapat menghapus kategori yang memiliki produk
     */
    public function test_cannot_delete_category_with_products(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /**
     * Test kategori diurutkan berdasarkan sort_order
     */
    public function test_categories_ordered_by_sort_order(): void
    {
        $user = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Third', 'sort_order' => 3]);
        Category::factory()->create(['name' => 'First', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Second', 'sort_order' => 2]);

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('categories.0.name', 'First')
            ->where('categories.1.name', 'Second')
            ->where('categories.2.name', 'Third'));
    }

    /**
     * Test default sort_order saat create kategori
     */
    public function test_category_gets_default_sort_order(): void
    {
        $user = User::factory()->admin()->create();
        Category::factory()->create(['sort_order' => 5]);

        $categoryData = [
            'name' => 'Kategori Tanpa Sort Order',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->post(route('admin.categories.store'), $categoryData);

        $response->assertRedirect(route('admin.categories.index'));
        $category = Category::where('name', 'Kategori Tanpa Sort Order')->first();
        $this->assertEquals(6, $category->sort_order);
    }
}
