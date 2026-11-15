class CvController < ApplicationController
  allow_unauthenticated_access

  def show
    @section = "cv"
    @items = CvItem.by_section
  end
end
