class ErrorsController < ApplicationController
  allow_unauthenticated_access
  skip_before_action :redirect_trailing_slash

  def not_found
    render status: :not_found, formats: :html
  end
end
