class ApplicationController < ActionController::Base
  include Authentication

  before_action :redirect_trailing_slash
  before_action :no_store_when_logged_in

  helper_method :profile

  private
    def profile
      @profile ||= Profile.instance
    end

    # One canonical URL per page: /writing, not /writing/.
    def redirect_trailing_slash
      path = request.original_fullpath.split("?").first # request.path has the slash stripped by the router
      if request.get? && path != "/" && path.end_with?("/")
        redirect_to path.chomp("/"), status: :moved_permanently
      end
    end

    # When you're logged in, public pages also show drafts, so they mustn't be cached.
    def no_store_when_logged_in
      response.headers["Cache-Control"] = "no-store" if authenticated?
    end
end
