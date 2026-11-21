require "test_helper"

class PublicPagesTest < ActionDispatch::IntegrationTest
  setup do
    Profile.create!(name: "Ada Lovelace", headline: "Engineer · Analytical engines, notes", links: "GitHub https://github.com/ada")
    CvItem.create!(section: "experience", title: "Analyst", subtitle: "Engine Co", body: "- Wrote notes")
    CvItem.create!(section: "skill", body: "- Ruby\n- SQL")
    @live = Post.create!(title: "Live post", body: "Hello **world**", draft: false, published_on: Date.yesterday)
    @draft = Post.create!(title: "Draft post", draft: true)
    @scheduled = Post.create!(title: "Later post", draft: false, published_on: Date.tomorrow)
  end

  test "CV page" do
    get root_path
    assert_response :success
    assert_select "title", "Ada Lovelace · Engineer"
    assert_select ".cv-entry-title", /Analyst/
    assert_select ".cv-skills li", 2
    assert_select ".cv-contact a[href='https://github.com/ada']", "GitHub"
  end

  test "writing shows only published posts" do
    get posts_path
    assert_select ".post-list li", 1
    assert_select "a[href=?]", post_path("live-post")
  end

  test "post page renders Markdown" do
    get post_path(@live.slug)
    assert_select ".prose strong", "world"
  end

  test "drafts and scheduled posts are hidden" do
    get post_path(@draft.slug)
    assert_response :not_found
    get post_path(@scheduled.slug)
    assert_response :not_found
  end

  test "logged in, drafts are visible and not cached" do
    sign_in_as users(:admin)
    get post_path(@draft.slug)
    assert_response :success
    assert_select ".badge", "Draft: only you can see this"
    assert_equal "no-store", response.headers["Cache-Control"]
  end

  test "trailing slash redirects" do
    get "/writing/"
    assert_redirected_to "/writing"
    assert_response :moved_permanently
  end

  test "scripts are blocked" do
    get root_path
    assert_includes response.headers["Content-Security-Policy"], "script-src 'none'"
  end
end
