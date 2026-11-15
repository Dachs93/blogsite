Rails.application.routes.draw do
  root "cv#show"

  get "writing" => "posts#index", as: :posts
  get "writing/:slug" => "posts#show", as: :post, constraints: { slug: /[a-z0-9-]+/ }

  get "admin/login" => "sessions#new", as: :new_session
  post "admin/login" => "sessions#create", as: :session
  delete "admin/logout" => "sessions#destroy", as: :logout

  get "admin" => redirect("/admin/posts"), as: :admin_root
  namespace :admin do
    resources :posts, except: :show
    resource :cv, only: :show, controller: "cv"
    resource :profile, only: :update, path: "cv/profile"
    resources :cv_items, path: "cv/items", except: %i[index show] do
      post :move, on: :member
    end
  end

  # Health check for the deploy: 200 if the app boots.
  get "up" => "rails/health#show", as: :rails_health_check
end
