module Admin
  class BaseController < ApplicationController
    before_action do
      @section = "admin"
      response.headers["Cache-Control"] = "no-store"
      response.headers["X-Robots-Tag"] = "noindex, nofollow"
    end
  end
end
