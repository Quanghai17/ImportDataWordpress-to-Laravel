<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use TCG\Voyager\Models\Category;
use TCG\Voyager\Models\Post;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{

  public function importFromWordpressAPI()
  {
    $page = 1;
    $perPage = 10;
    do {
      $response = Http::get('https://vietbacact.edu.vn/wp-json/wp/v2/posts', [
        '_embed' => true,
        'page' => $page,
        'per_page' => $perPage,
      ]);
      $posts = $response->json();

      foreach ($posts as $post) {
        try {
          $imageUrl = $post['_embedded']['wp:featuredmedia'][0]['source_url'];
          $imagePath = $this->getImage($imageUrl);

          $newPost = new Post();
          $newPost->title = $post['title']['rendered'];
          $newPost->seo_title = $post['title']['rendered'];
          $newPost->slug = $post['slug'];
          $newPost->excerpt = $post['excerpt']['rendered'];
          $newPost->meta_description = $post['excerpt']['rendered'];
          $newPost->meta_keywords = $post['excerpt']['rendered'];
          $newPost->body = $post['content']['rendered'];
          $newPost->created_at = $post['date'];
          $newPost->status = 'PUBLISHED';
          $newPost->updated_at = $post['modified'];
          $newPost->image = $imagePath;

          $newPost->save();

          $content = $post['content']['rendered'];
          preg_match_all('/<img.*?src=\"(.*?)\".*?>/i', $content, $matches);
          foreach ($matches[1] as $image_url) {
            try {
              $filename = basename($image_url);
              $image_content = file_get_contents($image_url);
              Storage::put('public/' . $filename, $image_content);
              $content = str_replace($image_url, 'http://localhost/Backendphp7433/public/storage/' . $filename, $content);

              $newImagePath = $this->getImageFromContent($image_url);
              // dd($newImagePath);
              $content = str_replace($image_url, $newImagePath, $content);
              // dd($content);
            } catch (\Exception $e) {

              Log::error('Error fetching image: ' . $image_url . ' - ' . $e->getMessage());

              continue;
            }
          }

          $newPost->body = $content;
          $newPost->save();

          if (isset($post['categories']) && is_array($post['categories']) && count($post['categories']) > 0) {
            foreach ($post['categories'] as $categoryId) {
              $category = Category::where('wordpress_id', '=', $categoryId)->first();
              // dd($category);
              $newPost->category()->associate($category);;
            }
          }

          $newPost->save();
        } catch (\Exception $e) {
          // echo "Đã xảy ra lỗi: " . $e->getMessage();
          continue;
        }
      }

      $page++;
    } while (!empty($posts));
  }

  public function getImageFromContent($imageUrl)
    {
        if (!empty($imageUrl)) {
          $filename = time() . '_' . uniqid() . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);

          try {
              $imageContents = Http::get($imageUrl);
              $path = 'posts/' . date('FY') . '/';
              Storage::disk(config('voyager.storage.disk'))->put($path . $filename, $imageContents);

              $resize_width = 1800;
              $resize_height = null;
              $img = Image::make(storage_path('app/public/' . $path . $filename))
                  ->resize($resize_width, $resize_height, function ($constraint) {
                      $constraint->aspectRatio();
                      $constraint->upsize();
                  })
                  ->encode(pathinfo($filename, PATHINFO_EXTENSION), 75);

              Storage::disk(config('voyager.storage.disk'))->put($path . $filename, (string)$img, 'public');

              return  'storage/' . $path . $filename;
          } catch (\Exception $e) {
              Log::error('Error fetching image: ' . $imageUrl . ' - ' . $e->getMessage());
              return $imageUrl; // Trả về đường dẫn gốc nếu có lỗi
          }
      }

      return $imageUrl;
    }

    public function getImage($imageUrl)
    {
        if (!empty($imageUrl)) {
            $filename = time() . '_' . uniqid() . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
    
            try {
                $imageContents = Http::get($imageUrl);
                $path = 'posts/' . date('FY') . '/';
                Storage::disk(config('voyager.storage.disk'))->put($path . $filename, $imageContents);
    
                $resize_width = 1800;
                $resize_height = null;
                $img = Image::make(storage_path('app/public/' . $path . $filename))
                    ->resize($resize_width, $resize_height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode(pathinfo($filename, PATHINFO_EXTENSION), 75);
    
                Storage::disk(config('voyager.storage.disk'))->put($path . $filename, (string)$img, 'public');
    
                return $path . $filename;
            } catch (\Exception $e) {
                Log::error('Error fetching image: ' . $imageUrl . ' - ' . $e->getMessage());
                return $imageUrl; // Trả về đường dẫn gốc nếu có lỗi
            }
        }
    
        return $imageUrl;
    }

  public function saveCategoriesFromAPI()
  {

    $response = Http::get('https://vietbacact.edu.vn/wp-json/wp/v2/categories');
    $categories = $response->json();

    foreach ($categories as $category) {
      $newCategory = new Category();
      $newCategory->name = $category['name'];
      $newCategory->slug = $category['slug'];

      $newCategory->save();
    }
  }
}
