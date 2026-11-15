require "test_helper"

class AdminTest < ActionDispatch::IntegrationTest
  setup { sign_in_as users(:admin) }

  test "create, edit and delete a post" do
    post admin_posts_path, params: { post: { title: "Fáilte, World!", body: "Hi", published_on: "2026-01-02", draft: "false" } }
    created = Post.last
    assert_equal "failte-world", created.slug
    assert_not created.draft?
    assert_redirected_to edit_admin_post_path(created)

    patch admin_post_path(created), params: { post: { title: "Renamed" } }
    assert_equal "Renamed", created.reload.title

    delete admin_post_path(created)
    assert_redirected_to admin_posts_path
    assert_not Post.exists?(created.id)
  end

  test "invalid post shows field errors" do
    post admin_posts_path, params: { post: { title: "" } }
    assert_response :unprocessable_content
    assert_select ".field.has-error .field-error", "Give the post a title."
  end

  test "duplicate address is rejected" do
    Post.create!(title: "Same")
    post admin_posts_path, params: { post: { title: "Same" } }
    assert_select "#f-slug-error", /Another post already uses this address/
  end

  test "CV admin page" do
    get admin_cv_path
    assert_response :success
    assert_equal "no-store", response.headers["Cache-Control"]
  end

  test "save profile" do
    patch admin_profile_path, params: { profile: { name: "Ada", links: "GitHub https://github.com/ada" } }
    assert_redirected_to admin_cv_path
    assert_equal "Ada", Profile.instance.name
  end

  test "bad link line is rejected" do
    patch admin_profile_path, params: { profile: { name: "Ada", links: "just text" } }
    assert_response :unprocessable_content
    assert_select "#f-links-error", /needs a label/
  end

  test "skills is a single box" do
    post admin_cv_items_path, params: { cv_item: { section: "skill", body: "- Ruby", title: "ignored" } }
    item = CvItem.last
    assert_equal "", item.title
    get new_admin_cv_item_path(section: "skill")
    assert_redirected_to edit_admin_cv_item_path(item)
  end

  test "reorder entries" do
    first = CvItem.create!(section: "experience", title: "First")
    second = CvItem.create!(section: "experience", title: "Second")
    post move_admin_cv_item_path(second), params: { direction: "up" }
    assert_equal %w[Second First], CvItem.where(section: "experience").ordered.pluck(:title)
    assert first.reload.position > second.reload.position
  end
end
