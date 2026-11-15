require "test_helper"

class SessionsControllerTest < ActionDispatch::IntegrationTest
  setup { @user = users(:admin) }

  test "login page" do
    get new_session_path
    assert_response :success
    assert_equal "noindex, nofollow", response.headers["X-Robots-Tag"]
  end

  test "log in with valid credentials" do
    post session_path, params: { email_address: @user.email_address, password: "password" }
    assert_redirected_to admin_root_url
    assert cookies[:session_id]
  end

  test "log in with wrong password" do
    post session_path, params: { email_address: @user.email_address, password: "wrong" }
    assert_response :unauthorized
    assert_select ".notice.error", "Wrong email address or password."
    assert_nil cookies[:session_id]
  end

  test "log out" do
    sign_in_as @user
    delete logout_path
    assert_redirected_to new_session_path
    assert_empty cookies[:session_id]
  end

  test "admin needs a login" do
    get admin_posts_path
    assert_redirected_to new_session_path
  end
end
