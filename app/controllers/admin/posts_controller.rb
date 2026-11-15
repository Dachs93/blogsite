module Admin
  class PostsController < BaseController
    before_action :set_post, only: %i[edit update destroy]

    def index
      @posts = Post.newest_first
    end

    def new
      @post = Post.new(published_on: Date.current)
    end

    def create
      @post = Post.new(post_params)
      if @post.save
        redirect_to edit_admin_post_path(@post), notice: "Saved."
      else
        render :new, status: :unprocessable_content
      end
    end

    def edit
    end

    def update
      if @post.update(post_params)
        redirect_to edit_admin_post_path(@post), notice: "Saved."
      else
        render :edit, status: :unprocessable_content
      end
    end

    def destroy
      @post.destroy
      redirect_to admin_posts_path, notice: "Deleted.", status: :see_other
    end

    private
      def set_post
        @post = Post.find(params[:id])
      end

      def post_params
        params.expect(post: %i[title slug summary body published_on draft])
      end
  end
end
