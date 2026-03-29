defmodule PhoenixApiWeb.Plugs.RateLimitImports do
  import Plug.Conn
  import Phoenix.Controller

  alias PhoenixApi.RateLimiter

  def init(opts), do: opts

  def call(conn, _opts) do
    current_user = conn.assigns.current_user

    case RateLimiter.allow_import(current_user.id) do
      :ok ->
        conn

      {:error, :user_limit_exceeded} ->
        conn
        |> put_status(:too_many_requests)
        |> put_view(json: PhoenixApiWeb.ErrorJSON)
        |> render(:"429")
        |> halt()

      {:error, :global_limit_exceeded} ->
        conn
        |> put_status(:too_many_requests)
        |> put_view(json: PhoenixApiWeb.ErrorJSON)
        |> render(:"429")
        |> halt()
    end
  end
end
