class PostsController < ApplicationController
  allow_unauthenticated_access
  before_action { @section = "writing" }

  def index
    @posts = visible_posts.newest_first
  end

  def show
    @post = visible_posts.find_by!(slug: params[:slug])
  end

  private
    # Logged in, you also see drafts and scheduled posts.
    def visible_posts
      authenticated? ? Post.all : Post.published
    end
end
