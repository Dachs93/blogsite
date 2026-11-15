class SessionsController < ApplicationController
  allow_unauthenticated_access only: %i[new create]
  before_action :admin_headers
  rate_limit to: 5, within: 15.minutes, only: :create, with: -> {
    flash.now[:alert] = "Too many failed attempts. Try again in 15 minutes."
    render :new, status: :too_many_requests
  }

  def new
    redirect_to admin_root_path if authenticated?
  end

  def create
    if user = User.authenticate_by(params.permit(:email_address, :password))
      start_new_session_for user
      redirect_to after_authentication_url
    else
      flash.now[:alert] = "Wrong email address or password."
      render :new, status: :unauthorized
    end
  end

  def destroy
    terminate_session
    redirect_to new_session_path, notice: "You've logged out.", status: :see_other
  end

  private
    def admin_headers
      response.headers["Cache-Control"] = "no-store"
      response.headers["X-Robots-Tag"] = "noindex, nofollow"
    end
end
